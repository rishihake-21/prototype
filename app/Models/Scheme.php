<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    protected $fillable = [
        'name',
        'implemented_year',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function levels()
    {
        return $this->hasMany(SchemeLevel::class)->orderBy('sort_order');
    }

    public function programmes()
    {
        return $this->hasMany(Programme::class);
    }

    public function assessmentComponents()
    {
        return $this->hasMany(SchemeAssessmentComponent::class)->orderBy('display_order');
    }

    public function learningComponents()
    {
        return $this->hasMany(SchemeLearningComponent::class)->orderBy('display_order');
    }

    public function getAssessmentTree()
    {
        $components = $this->assessmentComponents()->orderBy('display_order')->get();
        $grouped = $components->groupBy('parent_id');

        $buildTree = function ($parentId = null) use (&$buildTree, $grouped) {
            $nodes = [];
            if ($grouped->has($parentId)) {
                foreach ($grouped[$parentId] as $c) {
                    $nodes[] = [
                        'id' => $c->id,
                        'name' => $c->component_name,
                        'code' => $c->component_code,
                        'children' => $buildTree($c->id)
                    ];
                }
            }
            return $nodes;
        };

        return $buildTree(null);
    }

    public function getHeaderRows()
    {
        $rows = [[], [], []];
        $allComponents = $this->assessmentComponents()->orderBy('display_order')->get();
        $l1Nodes = $allComponents->where('parent_id', null);

        foreach ($l1Nodes as $l1) {
            $l2Nodes = $allComponents->where('parent_id', $l1->id);
            $l1Span = 0;

            if ($l2Nodes->isEmpty()) {
                $rows[0][] = [
                    'id' => $l1->id,
                    'name' => $l1->component_name,
                    'code' => $l1->component_code,
                    'colspan' => 1,
                    'rowspan' => 3,
                    'is_leaf' => true
                ];
                continue;
            }

            foreach ($l2Nodes as $l2) {
                $l3Nodes = $allComponents->where('parent_id', $l2->id);
                if ($l3Nodes->isEmpty()) {
                    $l1Span += 1;
                } else {
                    $l1Span += $l3Nodes->count();
                }
            }

            $rows[0][] = [
                'id' => $l1->id,
                'name' => $l1->component_name,
                'code' => $l1->component_code,
                'colspan' => $l1Span,
                'rowspan' => 1,
                'is_leaf' => false
            ];

            foreach ($l2Nodes as $l2) {
                $l3Nodes = $allComponents->where('parent_id', $l2->id);
                if ($l3Nodes->isEmpty()) {
                    $rows[1][] = [
                        'id' => $l2->id,
                        'name' => $l2->component_name,
                        'code' => $l2->component_code,
                        'colspan' => 1,
                        'rowspan' => 2,
                        'is_leaf' => true
                    ];
                } else {
                    $rows[1][] = [
                        'id' => $l2->id,
                        'name' => $l2->component_name,
                        'code' => $l2->component_code,
                        'colspan' => $l3Nodes->count(),
                        'rowspan' => 1,
                        'is_leaf' => false
                    ];

                    foreach ($l3Nodes as $l3) {
                        $rows[2][] = [
                            'id' => $l3->id,
                            'name' => $l3->component_name,
                            'code' => $l3->component_code,
                            'colspan' => 1,
                            'rowspan' => 1,
                            'is_leaf' => true
                        ];
                    }
                }
            }
        }

        // Return only non-empty rows for exact structural match
        return array_values(array_filter($rows, function ($r) { return count($r) > 0; }));
    }

    public function getLeafColumns()
    {
        $leaves = [];
        $allComponents = $this->assessmentComponents()->orderBy('display_order')->get();
        $l1Nodes = $allComponents->where('parent_id', null);

        foreach ($l1Nodes as $l1) {
            $l2Nodes = $allComponents->where('parent_id', $l1->id);
            if ($l2Nodes->isEmpty()) {
                $leaves[] = [
                    'id' => $l1->id,
                    'name' => $l1->component_name,
                    'code' => $l1->component_code
                ];
            } else {
                foreach ($l2Nodes as $l2) {
                    $l3Nodes = $allComponents->where('parent_id', $l2->id);
                    if ($l3Nodes->isEmpty()) {
                        $leaves[] = [
                            'id' => $l2->id,
                            'name' => $l2->component_name,
                            'code' => $l2->component_code
                        ];
                    } else {
                        foreach ($l3Nodes as $l3) {
                            $leaves[] = [
                                'id' => $l3->id,
                                'name' => $l3->component_name,
                                'code' => $l3->component_code
                            ];
                        }
                    }
                }
            }
        }

        return $leaves;
    }

    /**
     * "Add/Edit Course" should only capture assessment max-marks columns, not
     * scheme fields like learning hours, credits, paper duration, totals, mins, etc.
     *
     * Prefer metadata fields on scheme_assessment_components (value_kind/is_input/contributes_to_total).
     * Fall back to a pragmatic name-based heuristic for older schemes.
     */
    public function getCourseAssessmentLeafColumns(): array
    {
        $root = $this->findTopLevelComponentByName('Assessment Scheme');
        if (!$root) {
            // Fallback: best-effort filter from all leaves.
            return array_values(array_filter($this->getLeafColumns(), function ($leaf) {
                return $this->isCourseMarksLeafName($leaf['name'] ?? '');
            }));
        }

        return $this->getLeafColumnsUnder($root->id, function (SchemeAssessmentComponent $leaf) {
            return $this->isCourseAssessmentInputLeaf($leaf);
        });
    }

    public function getCourseAssessmentHeaderRows(): array
    {
        $root = $this->findTopLevelComponentByName('Assessment Scheme');
        if (!$root) {
            // Fallback: render a flat header row for the filtered leaves.
            $leafCols = $this->getCourseAssessmentLeafColumns();
            if (empty($leafCols)) {
                return [];
            }

            return [[array_map(function ($leaf) {
                return [
                    'id' => $leaf['id'],
                    'name' => $leaf['name'],
                    'code' => $leaf['code'] ?? null,
                    'colspan' => 1,
                    'rowspan' => 1,
                    'is_leaf' => true,
                ];
            }, $leafCols)]];
        }

        return $this->getHeaderRowsUnder($root->id, function (SchemeAssessmentComponent $leaf) {
            return $this->isCourseAssessmentInputLeaf($leaf);
        });
    }

    /**
     * Returns leaf columns under a given node, in display_order, with an optional name filter.
     *
     * @param int $rootId
     * @param callable|null $leafFilter function(SchemeAssessmentComponent $leaf): bool
     * @return array<int, array{id:int,name:string,code:string|null}>
     */
    private function getLeafColumnsUnder(int $rootId, ?callable $leafFilter = null): array
    {
        $all = $this->assessmentComponents()->orderBy('display_order')->get();
        $byParent = $all->groupBy('parent_id');

        $includedLeafIds = [];
        $walk = function ($parentId) use (&$walk, $byParent, $leafFilter, &$includedLeafIds) {
            $children = $byParent->get($parentId, collect());
            foreach ($children as $c) {
                $grandChildren = $byParent->get($c->id, collect());
                if ($grandChildren->isEmpty()) {
                    if (!$leafFilter || (bool) $leafFilter($c)) {
                        $includedLeafIds[] = (int) $c->id;
                    }
                    continue;
                }
                $walk($c->id);
            }
        };

        $walk($rootId);
        if (empty($includedLeafIds)) {
            return [];
        }

        $idSet = array_fill_keys($includedLeafIds, true);
        $leaves = [];
        foreach ($all as $c) {
            if (isset($idSet[$c->id])) {
                $leaves[] = [
                    'id' => (int) $c->id,
                    'name' => (string) $c->component_name,
                    'code' => $c->component_code,
                ];
            }
        }
        return $leaves;
    }

    /**
     * Builds up to 3 header rows for a subtree, filtered to only include leaves that pass $leafNameFilter.
     */
    private function getHeaderRowsUnder(int $rootId, ?callable $leafFilter = null): array
    {
        $all = $this->assessmentComponents()->orderBy('display_order')->get();
        $byParent = $all->groupBy('parent_id');

        $root = $all->firstWhere('id', $rootId);
        if (!$root) {
            return [];
        }

        $countIncludedLeaves = function ($nodeId) use (&$countIncludedLeaves, $byParent, $leafFilter) {
            $children = $byParent->get($nodeId, collect());
            if ($children->isEmpty()) {
                return 0;
            }

            $sum = 0;
            foreach ($children as $c) {
                $grandChildren = $byParent->get($c->id, collect());
                if ($grandChildren->isEmpty()) {
                    if (!$leafFilter || (bool) $leafFilter($c)) {
                        $sum += 1;
                    }
                    continue;
                }
                $sum += $countIncludedLeaves($c->id);
            }
            return $sum;
        };

        $rows = [[], [], []];
        $rootLeafCount = $countIncludedLeaves($rootId);
        if ($rootLeafCount <= 0) {
            return [];
        }

        $rows[0][] = [
            'id' => (int) $root->id,
            'name' => (string) $root->component_name,
            'code' => $root->component_code,
            'colspan' => $rootLeafCount,
            'rowspan' => 1,
            'is_leaf' => false,
        ];

        $l2Nodes = $byParent->get($rootId, collect());
        foreach ($l2Nodes as $l2) {
            $leafCount = $countIncludedLeaves($l2->id);
            if ($leafCount <= 0) {
                continue;
            }

            $l3Nodes = $byParent->get($l2->id, collect());
            if ($l3Nodes->isEmpty()) {
                // Leaf at L2 (rare in our usage)
                if (!$leafFilter || (bool) $leafFilter($l2)) {
                    $rows[1][] = [
                        'id' => (int) $l2->id,
                        'name' => (string) $l2->component_name,
                        'code' => $l2->component_code,
                        'colspan' => 1,
                        'rowspan' => 2,
                        'is_leaf' => true,
                    ];
                }
                continue;
            }

            $rows[1][] = [
                'id' => (int) $l2->id,
                'name' => (string) $l2->component_name,
                'code' => $l2->component_code,
                'colspan' => $leafCount,
                'rowspan' => 1,
                'is_leaf' => false,
            ];

            foreach ($l3Nodes as $l3) {
                $grandChildren = $byParent->get($l3->id, collect());
                if (!$grandChildren->isEmpty()) {
                    // Depth > 3 not supported by current table renderer; ignore safely.
                    continue;
                }
                if ($leafFilter && !(bool) $leafFilter($l3)) {
                    continue;
                }

                $rows[2][] = [
                    'id' => (int) $l3->id,
                    'name' => (string) $l3->component_name,
                    'code' => $l3->component_code,
                    'colspan' => 1,
                    'rowspan' => 1,
                    'is_leaf' => true,
                ];
            }
        }

        return array_values(array_filter($rows, function ($r) { return count($r) > 0; }));
    }

    private function isCourseAssessmentInputLeaf(SchemeAssessmentComponent $leaf): bool
    {
        $valueKind = $leaf->getAttribute('value_kind');
        if ($valueKind !== null) {
            return $valueKind === 'marks'
                && (bool) $leaf->getAttribute('is_input')
                && (bool) $leaf->getAttribute('contributes_to_total');
        }

        return $this->isCourseMarksLeafName((string) $leaf->component_name);
    }

    private function findTopLevelComponentByName(string $name): ?SchemeAssessmentComponent
    {
        $all = $this->assessmentComponents()->orderBy('display_order')->get();
        $l1 = $all->where('parent_id', null);
        $needle = mb_strtolower(trim($name));

        foreach ($l1 as $c) {
            if (mb_strtolower(trim((string) $c->component_name)) === $needle) {
                return $c;
            }
        }
        return null;
    }

    private function isCourseMarksLeafName(string $name): bool
    {
        $n = mb_strtolower(trim($name));
        if ($n === '') {
            return false;
        }

        // Only collect max-marks inputs. Everything else (hours/credits/duration/totals/mins) should not be editable marks.
        if (str_contains($n, '(max)')) {
            return true;
        }

        // Common label in the seeded K-Scheme: "Max (SLA)".
        if (str_starts_with($n, 'max') && str_contains($n, 'sla') && !str_contains($n, 'min')) {
            return true;
        }

        // Fallback for schemes that don't include "(Max)" in labels.
        if (str_starts_with($n, 'fa-') || str_starts_with($n, 'sa-')) {
            return true;
        }

        return false;
    }
}
