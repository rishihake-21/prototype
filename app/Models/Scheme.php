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
        return $this->buildHeaderRows($this->assessmentComponents()->orderBy('display_order')->get());
    }

    public function getLeafColumns()
    {
        return $this->buildLeafColumns($this->assessmentComponents()->orderBy('display_order')->get());
    }

    public function getAssessmentHeaderRows(): array
    {
        return $this->buildHeaderRows($this->getAssessmentComponentsSubset());
    }

    public function getAssessmentLeafColumns(): array
    {
        return $this->buildLeafColumns($this->getAssessmentComponentsSubset());
    }

    private function getAssessmentComponentsSubset()
    {
        $allComponents = $this->assessmentComponents()->orderBy('display_order')->get();
        $topLevel = $allComponents->where('parent_id', null);

        $assessmentRoot = $topLevel
            ->first(fn ($component) => str_contains(strtolower($component->component_name), 'assessment'));

        if (! $assessmentRoot) {
            return $allComponents;
        }

        return $allComponents->filter(function ($component) use ($allComponents, $assessmentRoot) {
            $current = $component;

            while ($current) {
                if ($current->id === $assessmentRoot->id) {
                    return true;
                }

                $current = $current->parent_id
                    ? $allComponents->firstWhere('id', $current->parent_id)
                    : null;
            }

            return false;
        })->values();
    }

    private function buildHeaderRows($components): array
    {
        $rows = [[], [], []];
        $l1Nodes = $components->where('parent_id', null);

        foreach ($l1Nodes as $l1) {
            $l2Nodes = $components->where('parent_id', $l1->id);
            $l1Span = 0;

            if ($l2Nodes->isEmpty()) {
                $rows[0][] = [
                    'id' => $l1->id,
                    'name' => $l1->component_name,
                    'code' => $l1->component_code,
                    'colspan' => 1,
                    'rowspan' => 3,
                    'is_leaf' => true,
                ];
                continue;
            }

            foreach ($l2Nodes as $l2) {
                $l3Nodes = $components->where('parent_id', $l2->id);
                $l1Span += $l3Nodes->isEmpty() ? 1 : $l3Nodes->count();
            }

            $rows[0][] = [
                'id' => $l1->id,
                'name' => $l1->component_name,
                'code' => $l1->component_code,
                'colspan' => $l1Span,
                'rowspan' => 1,
                'is_leaf' => false,
            ];

            foreach ($l2Nodes as $l2) {
                $l3Nodes = $components->where('parent_id', $l2->id);

                if ($l3Nodes->isEmpty()) {
                    $rows[1][] = [
                        'id' => $l2->id,
                        'name' => $l2->component_name,
                        'code' => $l2->component_code,
                        'colspan' => 1,
                        'rowspan' => 2,
                        'is_leaf' => true,
                    ];
                    continue;
                }

                $rows[1][] = [
                    'id' => $l2->id,
                    'name' => $l2->component_name,
                    'code' => $l2->component_code,
                    'colspan' => $l3Nodes->count(),
                    'rowspan' => 1,
                    'is_leaf' => false,
                ];

                foreach ($l3Nodes as $l3) {
                    $rows[2][] = [
                        'id' => $l3->id,
                        'name' => $l3->component_name,
                        'code' => $l3->component_code,
                        'colspan' => 1,
                        'rowspan' => 1,
                        'is_leaf' => true,
                    ];
                }
            }
        }

        return array_values(array_filter($rows, fn ($row) => count($row) > 0));
    }

    private function buildLeafColumns($components): array
    {
        $leaves = [];
        $l1Nodes = $components->where('parent_id', null);

        foreach ($l1Nodes as $l1) {
            $l2Nodes = $components->where('parent_id', $l1->id);

            if ($l2Nodes->isEmpty()) {
                $leaves[] = [
                    'id' => $l1->id,
                    'name' => $l1->component_name,
                    'code' => $l1->component_code,
                ];
                continue;
            }

            foreach ($l2Nodes as $l2) {
                $l3Nodes = $components->where('parent_id', $l2->id);

                if ($l3Nodes->isEmpty()) {
                    $leaves[] = [
                        'id' => $l2->id,
                        'name' => $l2->component_name,
                        'code' => $l2->component_code,
                    ];
                    continue;
                }

                foreach ($l3Nodes as $l3) {
                    $leaves[] = [
                        'id' => $l3->id,
                        'name' => $l3->component_name,
                        'code' => $l3->component_code,
                    ];
                }
            }
        }

        return $leaves;
    }
}
