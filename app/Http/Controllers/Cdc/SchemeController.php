<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Scheme;
use App\Models\SchemeLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SchemeController extends Controller
{
    public function index()
    {
        $schemes = Scheme::withCount('programmes')->latest()->get();
        return view('cdc.schemes.index', compact('schemes'));
    }

    public function create()
    {
        return view('cdc.schemes.create', ['scheme' => null]);
    }

    public function store(Request $request)
    {
        $currentYear = now()->year;
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schemes,name',
            'implemented_year' => 'nullable|integer|min:' . $currentYear,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'learning_structure' => 'nullable|array',
            'learning_structure.*.name' => 'required|string|max:100',
            'learning_structure.*.children' => 'nullable|array',
            'learning_structure.*.children.*.name' => 'required|string|max:100',
            'learning_structure.*.children.*.columns' => 'nullable|array',
            'learning_structure.*.children.*.columns.*' => 'nullable',
            'assessment_structure' => 'nullable|array',
            'assessment_structure.*.name' => 'required|string|max:100',
            'assessment_structure.*.children' => 'nullable|array',
            'assessment_structure.*.children.*.name' => 'required|string|max:100',
            'assessment_structure.*.children.*.columns' => 'nullable|array',
            'assessment_structure.*.children.*.columns.*' => 'nullable',
            // Backward compatibility: old single-tree input.
            'scheme_structure' => 'nullable|array',
            'levels' => 'required|array|min:1',
            'levels.*.level_code' => 'required|string|max:20',
            'levels.*.level_name' => 'required|string|max:255',
            'levels.*.sort_order' => 'required|integer',
        ], [
            'implemented_year.min' => "Past-year schemes are not allowed. Select {$currentYear} or later.",
        ]);

        $scheme = Scheme::create([
            'name' => $validated['name'],
            'implemented_year' => $validated['implemented_year'],
            'description' => $validated['description'],
            'is_active' => $request->has('is_active'),
        ]);

        foreach ($request->input('levels', []) as $levelData) {
            $scheme->levels()->create($levelData);
        }

        [$learningStructure, $assessmentStructure] = $this->resolveStructures($request);
        $this->persistStructure($scheme, 'learning', $learningStructure);
        $this->persistStructure($scheme, 'assessment', $assessmentStructure);

        return redirect()->route('cdc.schemes.index')->with('success', 'Scheme created successfully.');
    }

    public function edit(Scheme $scheme)
    {
        $scheme->load('levels', 'assessmentComponents', 'learningComponents');
        return view('cdc.schemes.create', compact('scheme'));
    }

    public function update(Request $request, Scheme $scheme)
    {
        $currentYear = now()->year;
        $minYear = $scheme->implemented_year !== null && (int) $scheme->implemented_year < $currentYear
            ? (int) $scheme->implemented_year
            : $currentYear;
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schemes,name,' . $scheme->id,
            'implemented_year' => 'nullable|integer|min:' . $minYear,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'learning_structure' => 'nullable|array',
            'learning_structure.*.name' => 'required|string|max:100',
            'learning_structure.*.children' => 'nullable|array',
            'learning_structure.*.children.*.name' => 'required|string|max:100',
            'learning_structure.*.children.*.columns' => 'nullable|array',
            'learning_structure.*.children.*.columns.*' => 'nullable',
            'assessment_structure' => 'nullable|array',
            'assessment_structure.*.name' => 'required|string|max:100',
            'assessment_structure.*.children' => 'nullable|array',
            'assessment_structure.*.children.*.name' => 'required|string|max:100',
            'assessment_structure.*.children.*.columns' => 'nullable|array',
            'assessment_structure.*.children.*.columns.*' => 'nullable',
            // Backward compatibility: old single-tree input.
            'scheme_structure' => 'nullable|array',
            'levels' => 'required|array|min:1',
            'levels.*.id' => 'nullable|exists:scheme_levels,id',
            'levels.*.level_code' => 'required|string|max:20',
            'levels.*.level_name' => 'required|string|max:255',
            'levels.*.sort_order' => 'required|integer',
        ], [
            'implemented_year.min' => $minYear < $currentYear
                ? "Legacy schemes can keep their current implemented year ({$minYear}), but cannot be moved further into the past."
                : "Past-year schemes are not allowed. Select {$currentYear} or later.",
        ]);

        [$learningStructure, $assessmentStructure] = $this->resolveStructures($request);

        DB::transaction(function () use ($scheme, $validated, $request, $learningStructure, $assessmentStructure) {
            $scheme->update([
                'name' => $validated['name'],
                'implemented_year' => $validated['implemented_year'],
                'description' => $validated['description'],
                'is_active' => $request->has('is_active'),
            ]);

            $this->syncLevels($scheme, $request->input('levels', []));
            $this->syncStructure($scheme, 'learning', $learningStructure);
            $this->syncStructure($scheme, 'assessment', $assessmentStructure);
        });

        return redirect()->route('cdc.schemes.index')->with('success', 'Scheme updated successfully.');
    }

    public function destroy(Scheme $scheme)
    {
        if ($scheme->programmes()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete scheme because it is being used by programmes.');
        }

        DB::transaction(function () use ($scheme) {
            $scheme->learningComponents()->delete();
            $scheme->assessmentComponents()->delete();
            $scheme->levels()->delete();
            $scheme->delete();
        });

        return redirect()->route('cdc.schemes.index')->with('success', 'Scheme deleted successfully.');
    }

    /**
     * Determine learning vs assessment structures from request, with backward-compatible auto-splitting.
     *
     * @return array{0: array, 1: array}
     */
    private function resolveStructures(Request $request): array
    {
        $learning = $request->input('learning_structure', null);
        $assessment = $request->input('assessment_structure', null);

        if ($learning !== null || $assessment !== null) {
            return [$learning ?: [], $assessment ?: []];
        }

        // Old single-tree input. Split by L1 heading name.
        $legacy = $request->input('scheme_structure', []);
        if (!is_array($legacy)) {
            return [[], []];
        }

        $learningOut = [];
        $assessmentOut = [];
        foreach ($legacy as $l1) {
            $name = strtolower(trim((string) ($l1['name'] ?? '')));
            if ($name === 'assessment scheme') {
                $assessmentOut[] = $l1;
            } else {
                $learningOut[] = $l1;
            }
        }
        return [$learningOut, $assessmentOut];
    }

    private function persistStructure(Scheme $scheme, string $kind, array $structure): void
    {
        $rel = $kind === 'learning' ? $scheme->learningComponents() : $scheme->assessmentComponents();
        $useAssessmentMeta = $kind === 'assessment' && $this->supportsAssessmentMeta();
        $useLearningMeta = $kind === 'learning' && $this->supportsLearningMeta();

        $displayOrder = 0;
        foreach ($structure as $l1Data) {
            if (trim((string) ($l1Data['name'] ?? '')) === '') {
                continue;
            }

            $l1Name = (string) $l1Data['name'];
            $l1Attrs = [
                'component_code' => Str::slug($l1Name),
                'component_name' => $l1Name,
                'display_order' => $displayOrder++,
            ];
            if ($useAssessmentMeta) {
                $l1Attrs += $this->assessmentGroupMeta();
            }
            $l1Node = $rel->create($l1Attrs);

            $children = $l1Data['children'] ?? [];
            if (!is_array($children) || empty($children)) {
                continue;
            }

            foreach ($children as $l2Data) {
                if (trim((string) ($l2Data['name'] ?? '')) === '') {
                    continue;
                }

                $l2Name = (string) $l2Data['name'];
                $l2Attrs = [
                    'parent_id' => $l1Node->id,
                    'component_code' => Str::slug($l1Name . ' ' . $l2Name),
                    'component_name' => $l2Name,
                    'display_order' => $displayOrder++,
                ];
                if ($useAssessmentMeta) {
                    $l2Attrs += $this->assessmentGroupMeta();
                }
                $l2Node = $rel->create($l2Attrs);

                $cols = $l2Data['columns'] ?? [];
                if (!is_array($cols) || empty($cols)) {
                    continue;
                }

                foreach ($cols as $columnData) {
                    $column = $kind === 'learning'
                        ? $this->normalizeLearningColumn($columnData)
                        : $this->normalizeAssessmentColumn($columnData);
                    if ($column['name'] === '') {
                        continue;
                    }

                    $leafAttrs = [
                        'parent_id' => $l2Node->id,
                        'component_code' => Str::slug($l1Name . ' ' . $l2Name . ' ' . $column['name']),
                        'component_name' => $column['name'],
                        'display_order' => $displayOrder++,
                    ];
                    if ($useAssessmentMeta) {
                        $leafAttrs += $this->resolveAssessmentLeafMeta($column['name'], $column);
                    } elseif ($useLearningMeta) {
                        $leafAttrs += $this->resolveLearningLeafMeta($column['name'], $column);
                    }

                    $rel->create($leafAttrs);
                }
            }
        }
    }

    private function syncLevels(Scheme $scheme, array $inputLevels): void
    {
        $existingLevelIds = $scheme->levels()->pluck('id')->toArray();
        $keptIds = [];

        foreach ($inputLevels as $levelData) {
            if (!empty($levelData['id'])) {
                $level = SchemeLevel::where('scheme_id', $scheme->id)->find($levelData['id']);
                if ($level) {
                    $level->update($levelData);
                    $keptIds[] = $level->id;
                    continue;
                }
            }

            $newLevel = $scheme->levels()->create($levelData);
            $keptIds[] = $newLevel->id;
        }

        $toDelete = array_diff($existingLevelIds, $keptIds);
        if (!empty($toDelete)) {
            SchemeLevel::whereIn('id', $toDelete)->delete();
        }
    }

    private function syncStructure(Scheme $scheme, string $kind, array $structure): void
    {
        $rel = $kind === 'learning' ? $scheme->learningComponents() : $scheme->assessmentComponents();
        $useAssessmentMeta = $kind === 'assessment' && $this->supportsAssessmentMeta();
        $useLearningMeta = $kind === 'learning' && $this->supportsLearningMeta();
        $existing = $rel->orderBy('display_order')->get();

        if ($kind === 'assessment' && $existing->isNotEmpty()) {
            $hasLinkedCourseMarks = DB::table('course_assessments')
                ->whereIn('component_id', $existing->pluck('id'))
                ->exists();

            if ($hasLinkedCourseMarks) {
                $currentStructure = $this->exportStructure($existing);
                if ($this->normalizeStructure($currentStructure) !== $this->normalizeStructure($structure)) {
                    throw ValidationException::withMessages([
                        'assessment_structure' => 'This scheme already has subject marks stored against its assessment columns. Edit the scheme metadata if needed, but do not change the assessment structure until the linked course data is migrated.',
                    ]);
                }

                return;
            }
        }

        $byParent = $existing->groupBy('parent_id');
        $displayOrder = 0;

        $syncChildren = function ($parentId, array $items, array $path = []) use (&$syncChildren, $rel, $byParent, $kind, $useAssessmentMeta, $useLearningMeta, &$displayOrder) {
            $siblings = $byParent->get($parentId, collect())->values();
            $usedSiblingIds = [];
            $preferPositionalMatch = count(array_values($items)) === $siblings->count();

            foreach (array_values($items) as $index => $item) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $match = null;
                if ($preferPositionalMatch && isset($siblings[$index]) && !in_array($siblings[$index]->id, $usedSiblingIds, true)) {
                    $match = $siblings[$index];
                }

                if (!$match) {
                    $match = $siblings
                        ->first(function ($node) use ($name, &$usedSiblingIds) {
                            return !in_array($node->id, $usedSiblingIds, true)
                                && trim((string) $node->component_name) === $name;
                        });
                }

                $attrs = [
                    'parent_id' => $parentId,
                    'component_code' => Str::slug(implode(' ', array_merge($path, [$name]))),
                    'component_name' => $name,
                    'display_order' => $displayOrder++,
                ];

                if ($useAssessmentMeta) {
                    $attrs += empty($path)
                        ? $this->assessmentGroupMeta()
                        : ((isset($item['columns']) || isset($item['children']))
                            ? $this->assessmentGroupMeta()
                            : $this->resolveAssessmentLeafMeta($name, $item));
                } elseif ($useLearningMeta) {
                    $attrs += empty($path)
                        ? $this->learningGroupMeta()
                        : ((isset($item['columns']) || isset($item['children']))
                            ? $this->learningGroupMeta()
                            : $this->resolveLearningLeafMeta($name, $item));
                }

                if ($match) {
                    $match->update($attrs);
                    $usedSiblingIds[] = $match->id;
                    $node = $match->fresh();
                } else {
                    $node = $rel->create($attrs);
                }

                $children = [];
                if (array_key_exists('children', $item) && is_array($item['children'])) {
                    foreach ($item['children'] as $child) {
                        $children[] = [
                            'name' => $child['name'] ?? '',
                            'children' => [],
                            'columns' => is_array($child['columns'] ?? null) ? $child['columns'] : [],
                        ];
                    }
                } elseif (array_key_exists('columns', $item) && is_array($item['columns'])) {
                    foreach ($item['columns'] as $columnData) {
                        $column = $kind === 'learning'
                            ? $this->normalizeLearningColumn($columnData)
                            : $this->normalizeAssessmentColumn($columnData);
                        if ($column['name'] === '') {
                            continue;
                        }
                        $children[] = $column;
                    }
                }

                $syncChildren($node->id, $children, array_merge($path, [$name]));
            }

            $remaining = $siblings->reject(fn ($node) => in_array($node->id, $usedSiblingIds, true))->values();
            if ($remaining->isNotEmpty()) {
                $this->deleteRemovedNodes($remaining, $kind, $byParent);
            }
        };

        $normalized = [];
        foreach ($structure as $l1) {
            $normalized[] = [
                'name' => $l1['name'] ?? '',
                'children' => is_array($l1['children'] ?? null) ? $l1['children'] : [],
            ];
        }

        $syncChildren(null, $normalized);
    }

    private function deleteRemovedNodes($nodes, string $kind, $byParent): void
    {
        foreach ($nodes as $node) {
            if ($kind === 'assessment') {
                $ids = $this->collectNodeIds((int) $node->id, $byParent);
                $linkedCourseCount = DB::table('course_assessments')
                    ->whereIn('component_id', $ids)
                    ->count();

                if ($linkedCourseCount > 0) {
                    throw ValidationException::withMessages([
                        'assessment_structure' => 'Cannot remove assessment components that are already used by course marks. Update the scheme without deleting existing assessment columns, or migrate the affected course data first.',
                    ]);
                }
            }

            $node->delete();
        }
    }

    private function collectNodeIds(int $rootId, $byParent): array
    {
        $ids = [$rootId];
        foreach ($byParent->get($rootId, collect()) as $child) {
            $ids = array_merge($ids, $this->collectNodeIds((int) $child->id, $byParent));
        }

        return array_values(array_unique($ids));
    }

    private function exportStructure($nodes): array
    {
        $out = [];
        $l1Nodes = $nodes->where('parent_id', null);

        foreach ($l1Nodes as $l1) {
            $l1Data = ['name' => $l1->component_name, 'children' => []];
            $l2Nodes = $nodes->where('parent_id', $l1->id);

            foreach ($l2Nodes as $l2) {
                $l2Data = ['name' => $l2->component_name, 'columns' => []];
                $l3Nodes = $nodes->where('parent_id', $l2->id);

                foreach ($l3Nodes as $l3) {
                    $l2Data['columns'][] = [
                        'name' => $l3->component_name,
                        'semantic_key' => $l3->semantic_key,
                        'usage_scope' => $l3->usage_scope,
                        'entry_mode' => $l3->entry_mode,
                        'total_role' => $l3->total_role,
                    ];
                }

                $l1Data['children'][] = $l2Data;
            }

            $out[] = $l1Data;
        }

        return $out;
    }

    private function normalizeStructure(array $structure): array
    {
        return array_values(array_map(function ($l1) {
            return [
                'name' => trim((string) ($l1['name'] ?? '')),
                'children' => array_values(array_map(function ($l2) {
                    return [
                        'name' => trim((string) ($l2['name'] ?? '')),
                        'columns' => array_values(array_map(
                            function ($col) {
                                $normalized = $this->normalizeAssessmentColumn($col);

                                return [
                                    'name' => $normalized['name'],
                                    'semantic_key' => $normalized['semantic_key'],
                                    'usage_scope' => $normalized['usage_scope'],
                                ];
                            },
                            is_array($l2['columns'] ?? null) ? $l2['columns'] : []
                        )),
                    ];
                }, is_array($l1['children'] ?? null) ? $l1['children'] : [])),
            ];
        }, $structure));
    }

    private function assessmentGroupMeta(): array
    {
        return [
            'value_kind' => 'group',
            'entry_mode' => 'readonly',
            'total_role' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
        ];
    }

    private function learningGroupMeta(): array
    {
        return [
            'value_kind' => 'group',
            'entry_mode' => 'readonly',
            'total_role' => 'group',
            'usage_scope' => 'display_only',
            'semantic_key' => null,
        ];
    }

    private function inferAssessmentLeafMeta(string $name): array
    {
        $n = strtolower(trim($name));

        // Duration-like fields must not contribute to total marks.
        if ($n === 'paper duration' || str_contains($n, 'duration') || str_contains($n, 'hrs')) {
            return [
                'value_kind' => 'duration',
                'entry_mode' => 'input',
                'total_role' => 'informational',
                'is_input' => true,
                'contributes_to_total' => false,
            ];
        }

        // Marks-like leaf (default).
        $isTotalOrMin = str_contains($n, 'total') || str_contains($n, 'min');
        $isMax = str_contains($n, '(max)') || str_starts_with($n, 'fa-') || str_starts_with($n, 'sa-');
        $isSlaMax = str_starts_with($n, 'max') && str_contains($n, 'sla') && !str_contains($n, 'min');

        $isInput = ($isMax || $isSlaMax) && !$isTotalOrMin;

        return [
            'value_kind' => 'marks',
            'entry_mode' => $isInput ? 'input' : 'computed',
            'total_role' => $isTotalOrMin ? (str_contains($n, 'min') ? 'min_pass' : 'derived') : 'adds_to_total',
            'is_input' => $isInput,
            'contributes_to_total' => $isInput,
        ];
    }

    /**
     * @param mixed $column
     * @return array{name:string,semantic_key:?string,usage_scope:?string}
     */
    private function normalizeAssessmentColumn(mixed $column): array
    {
        if (is_array($column)) {
            return [
                'name' => trim((string) ($column['name'] ?? '')),
                'semantic_key' => $this->normalizeSemanticKey($column['semantic_key'] ?? null),
                'usage_scope' => $this->normalizeUsageScope($column['usage_scope'] ?? null),
            ];
        }

        return [
            'name' => trim((string) $column),
            'semantic_key' => null,
            'usage_scope' => null,
        ];
    }

    private function resolveAssessmentLeafMeta(string $name, array $column = []): array
    {
        $meta = $this->inferAssessmentLeafMeta($name);
        $semanticKey = $column['semantic_key'] ?? $this->inferSemanticKey($name);
        $usageScope = $column['usage_scope'] ?? $this->inferUsageScope($name, $meta);

        if ($semanticKey === 'paper_duration') {
            $meta['value_kind'] = 'duration';
            $meta['is_input'] = true;
            $meta['contributes_to_total'] = false;
        } elseif ($usageScope === 'course_definition') {
            $meta['value_kind'] = 'marks';
            $meta['is_input'] = true;
            $meta['contributes_to_total'] = !str_ends_with((string) $semanticKey, '_min')
                && $semanticKey !== 'total_marks';
        }

        return [
            'semantic_key' => $semanticKey,
            'usage_scope' => $usageScope,
        ] + $meta;
    }

    private function normalizeSemanticKey(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || strcasecmp($value, 'Custom / Not mapped') === 0) {
            return null;
        }

        return Str::snake($value);
    }

    private function normalizeUsageScope(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || strcasecmp($value, 'Custom / Not mapped') === 0) {
            return null;
        }

        return $value;
    }

    /**
     * @param mixed $column
     * @return array{name:string,semantic_key:?string,usage_scope:?string,entry_mode:?string,total_role:?string}
     */
    private function normalizeLearningColumn(mixed $column): array
    {
        if (is_array($column)) {
            return [
                'name' => trim((string) ($column['name'] ?? '')),
                'semantic_key' => $this->normalizeSemanticKey($column['semantic_key'] ?? null),
                'usage_scope' => $this->normalizeUsageScope($column['usage_scope'] ?? null),
                'entry_mode' => $this->normalizeTextValue($column['entry_mode'] ?? null),
                'total_role' => $this->normalizeTextValue($column['total_role'] ?? null),
            ];
        }

        return [
            'name' => trim((string) $column),
            'semantic_key' => null,
            'usage_scope' => null,
            'entry_mode' => null,
            'total_role' => null,
        ];
    }

    private function inferSemanticKey(string $name): ?string
    {
        $n = strtolower(trim($name));

        return match (true) {
            $n === 'paper duration' || str_contains($n, 'duration') || str_contains($n, 'hrs') => 'paper_duration',
            str_contains($n, 'fa-th') && str_contains($n, 'min') => 'fa_th_min',
            str_contains($n, 'fa-th') => 'fa_th_max',
            str_contains($n, 'sa-th') && str_contains($n, 'min') => 'sa_th_min',
            str_contains($n, 'sa-th') => 'sa_th_max',
            str_contains($n, 'fa-pr') && str_contains($n, 'min') => 'fa_pr_min',
            str_contains($n, 'fa-pr') => 'fa_pr_max',
            str_contains($n, 'sa-pr') && str_contains($n, 'min') => 'sa_pr_min',
            str_contains($n, 'sa-pr') => 'sa_pr_max',
            str_contains($n, 'sla') && str_contains($n, 'min') => 'sla_min',
            str_contains($n, 'sla') => 'sla_max',
            str_contains($n, 'tw') && str_contains($n, 'min') => 'tw_min',
            str_contains($n, 'tw') => 'tw_max',
            (str_contains($n, 'oral') || preg_match('/\bor\b/', $n) === 1) && str_contains($n, 'min') => 'oral_min',
            str_contains($n, 'oral') || preg_match('/\bor\b/', $n) === 1 => 'oral_max',
            str_contains($n, 'total') => 'total_marks',
            default => null,
        };
    }

    private function inferUsageScope(string $name, array $meta): string
    {
        if (($meta['value_kind'] ?? null) === 'duration') {
            return 'course_definition';
        }

        if (($meta['total_role'] ?? null) === 'min_pass') {
            return 'course_definition';
        }

        if (($meta['value_kind'] ?? null) === 'marks'
            && !empty($meta['is_input'])
            && !empty($meta['contributes_to_total'])) {
            return 'course_definition';
        }

        return 'display_only';
    }

    private function resolveLearningLeafMeta(string $name, array $column = []): array
    {
        $semanticKey = $column['semantic_key'] ?? $this->inferLearningSemanticKey($name);
        $usageScope = $column['usage_scope'] ?? $this->inferLearningUsageScope($semanticKey);
        $entryMode = $column['entry_mode'] ?? (($usageScope === 'display_only') ? 'computed' : 'input');
        $totalRole = $column['total_role'] ?? $this->inferLearningTotalRole($semanticKey);

        return [
            'semantic_key' => $semanticKey,
            'usage_scope' => $usageScope,
            'value_kind' => $this->inferLearningValueKind($semanticKey),
            'entry_mode' => $entryMode,
            'total_role' => $totalRole,
        ];
    }

    private function inferLearningSemanticKey(string $name): ?string
    {
        $n = strtolower(trim($name));

        return match (true) {
            $n === 'credits' => 'credits',
            $n === 'cl' || str_contains($n, 'classroom') => 'th_hours',
            $n === 'tu' || str_contains($n, 'tutorial') => 'tu_hours',
            $n === 'll' || $n === 'practical' || str_contains($n, 'laboratory') => 'pr_hours',
            str_contains($n, 'self learning') || str_contains($n, 'slh') => 'slh_hours',
            str_contains($n, 'notional') || str_contains($n, 'nlh') => 'nlh_hours',
            str_contains($n, 'total learning') || str_contains($n, 'total hrs') => 'total_hours',
            default => null,
        };
    }

    private function inferLearningUsageScope(?string $semanticKey): string
    {
        return match ($semanticKey) {
            'th_hours', 'tu_hours', 'pr_hours', 'credits', 'slh_hours', 'nlh_hours', 'total_hours' => 'syllabus',
            default => 'display_only',
        };
    }

    private function inferLearningValueKind(?string $semanticKey): string
    {
        return match ($semanticKey) {
            'credits' => 'credits',
            'th_hours', 'tu_hours', 'pr_hours', 'slh_hours', 'nlh_hours', 'total_hours' => 'hours',
            default => 'text',
        };
    }

    private function inferLearningTotalRole(?string $semanticKey): string
    {
        return match ($semanticKey) {
            'total_hours', 'nlh_hours' => 'derived',
            'credits' => 'summary',
            default => 'informational',
        };
    }

    private function normalizeTextValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function supportsLearningMeta(): bool
    {
        static $supported = null;
        if ($supported !== null) {
            return (bool) $supported;
        }

        $supported = Schema::hasColumn('scheme_learning_components', 'usage_scope')
            && Schema::hasColumn('scheme_learning_components', 'semantic_key')
            && Schema::hasColumn('scheme_learning_components', 'value_kind')
            && Schema::hasColumn('scheme_learning_components', 'entry_mode')
            && Schema::hasColumn('scheme_learning_components', 'total_role');

        return (bool) $supported;
    }

    private function supportsAssessmentMeta(): bool
    {
        static $supported = null;
        if ($supported !== null) {
            return (bool) $supported;
        }
        $supported = Schema::hasColumn('scheme_assessment_components', 'value_kind')
            && Schema::hasColumn('scheme_assessment_components', 'is_input')
            && Schema::hasColumn('scheme_assessment_components', 'contributes_to_total');
        return (bool) $supported;
    }
}
