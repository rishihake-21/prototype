<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Scheme;
use App\Models\SchemeLevel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
            'learning_structure.*.children.*.columns.*' => 'string|max:100',
            'assessment_structure' => 'nullable|array',
            'assessment_structure.*.name' => 'required|string|max:100',
            'assessment_structure.*.children' => 'nullable|array',
            'assessment_structure.*.children.*.name' => 'required|string|max:100',
            'assessment_structure.*.children.*.columns' => 'nullable|array',
            'assessment_structure.*.children.*.columns.*' => 'string|max:100',
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
            'learning_structure.*.children.*.columns.*' => 'string|max:100',
            'assessment_structure' => 'nullable|array',
            'assessment_structure.*.name' => 'required|string|max:100',
            'assessment_structure.*.children' => 'nullable|array',
            'assessment_structure.*.children.*.name' => 'required|string|max:100',
            'assessment_structure.*.children.*.columns' => 'nullable|array',
            'assessment_structure.*.children.*.columns.*' => 'string|max:100',
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

        $scheme->update([
            'name' => $validated['name'],
            'implemented_year' => $validated['implemented_year'],
            'description' => $validated['description'],
            'is_active' => $request->has('is_active'),
        ]);

        $inputLevels = $request->input('levels', []);
        $existingLevelIds = $scheme->levels()->pluck('id')->toArray();
        $keptIds = [];

        foreach ($inputLevels as $levelData) {
            if (!empty($levelData['id'])) {
                $level = SchemeLevel::where('scheme_id', $scheme->id)->find($levelData['id']);
                if ($level) {
                    $level->update($levelData);
                    $keptIds[] = $level->id;
                }
            }
            else {
                $newLevel = $scheme->levels()->create($levelData);
                $keptIds[] = $newLevel->id;
            }
        }

        $toDelete = array_diff($existingLevelIds, $keptIds);
        if (!empty($toDelete)) {
            SchemeLevel::whereIn('id', $toDelete)->delete();
        }

        [$learningStructure, $assessmentStructure] = $this->resolveStructures($request);
        $scheme->learningComponents()->delete();
        $scheme->assessmentComponents()->delete();
        $this->persistStructure($scheme, 'learning', $learningStructure);
        $this->persistStructure($scheme, 'assessment', $assessmentStructure);

        return redirect()->route('cdc.schemes.index')->with('success', 'Scheme updated successfully.');
    }

    public function destroy(Scheme $scheme)
    {
        if ($scheme->programmes()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete scheme because it is being used by programmes.');
        }

        $scheme->delete();
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

                foreach ($cols as $colName) {
                    if (trim((string) $colName) === '') {
                        continue;
                    }

                    $leafAttrs = [
                        'parent_id' => $l2Node->id,
                        'component_code' => Str::slug($l1Name . ' ' . $l2Name . ' ' . $colName),
                        'component_name' => (string) $colName,
                        'display_order' => $displayOrder++,
                    ];
                    if ($useAssessmentMeta) {
                        $leafAttrs += $this->inferAssessmentLeafMeta((string) $colName);
                    }

                    $rel->create($leafAttrs);
                }
            }
        }
    }

    private function assessmentGroupMeta(): array
    {
        return [
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
        ];
    }

    private function inferAssessmentLeafMeta(string $name): array
    {
        $n = strtolower(trim($name));

        // Duration-like fields must not contribute to total marks.
        if ($n === 'paper duration' || str_contains($n, 'duration') || str_contains($n, 'hrs')) {
            return [
                'value_kind' => 'duration',
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
            'is_input' => $isInput,
            'contributes_to_total' => $isInput,
        ];
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
