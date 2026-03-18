<?php

namespace Database\Seeders;

use App\Models\Scheme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MsbteKSchemeSeeder extends Seeder
{
    public function run(): void
    {
        $scheme = Scheme::updateOrCreate(
            ['name' => 'K-Scheme'],
            [
                'implemented_year' => 2026,
                'description' => 'MSBTE K-Scheme structure.',
                'is_active' => true,
            ]
        );

        $levels = [
            ['level_code' => '1', 'level_name' => 'Basic Courses', 'sort_order' => 1],
            ['level_code' => '2', 'level_name' => 'Foundation Courses', 'sort_order' => 2],
            ['level_code' => '3', 'level_name' => 'Allied Courses', 'sort_order' => 3],
            ['level_code' => '4', 'level_name' => 'Applied Courses', 'sort_order' => 4],
            ['level_code' => '5', 'level_name' => 'Diversified Courses', 'sort_order' => 5],
            ['level_code' => 'Au', 'level_name' => 'Audit Courses', 'sort_order' => 6],
        ];

        foreach ($levels as $level) {
            $scheme->levels()->updateOrCreate(
                ['level_code' => $level['level_code']],
                [
                    'level_name' => $level['level_name'],
                    'sort_order' => $level['sort_order'],
                ]
            );
        }

        $learningStructure = [
            [
                'name' => 'Learning Scheme',
                'children' => [
                    [
                        'name' => 'Actual Contact Hours / Week',
                        'columns' => [
                            ['name' => 'TH', 'semantic_key' => 'th_hours', 'usage_scope' => 'syllabus'],
                            ['name' => 'TU', 'semantic_key' => 'tu_hours', 'usage_scope' => 'syllabus'],
                            ['name' => 'PR', 'semantic_key' => 'pr_hours', 'usage_scope' => 'syllabus'],
                            ['name' => 'Total Hrs', 'semantic_key' => 'total_hours', 'usage_scope' => 'syllabus'],
                        ],
                    ],
                    [
                        'name' => 'Self Learning (Activity / Assignment / Micro Project)',
                        'columns' => [
                            ['name' => 'SLA', 'semantic_key' => 'slh_hours', 'usage_scope' => 'syllabus'],
                        ],
                    ],
                    [
                        'name' => 'Notional Learning Hours / Week',
                        'columns' => [
                            ['name' => 'NLH', 'semantic_key' => 'nlh_hours', 'usage_scope' => 'syllabus'],
                        ],
                    ],
                    [
                        'name' => 'Credits',
                        'columns' => [
                            ['name' => 'Credits', 'semantic_key' => 'credits', 'usage_scope' => 'syllabus'],
                        ],
                    ],
                ],
            ],
        ];

        $assessmentStructure = [
            [
                'name' => 'Assessment Scheme',
                'children' => [
                    [
                        'name' => 'Paper Duration',
                        'columns' => [
                            ['name' => 'Hrs', 'semantic_key' => 'paper_duration', 'usage_scope' => 'course_definition'],
                        ],
                    ],
                    [
                        'name' => 'Theory',
                        'columns' => [
                            ['name' => 'FA-TH (Max)', 'semantic_key' => 'fa_th_max', 'usage_scope' => 'course_definition'],
                            ['name' => 'FA-TH (Min)', 'semantic_key' => 'fa_th_min', 'usage_scope' => 'course_definition'],
                            ['name' => 'SA-TH (Max)', 'semantic_key' => 'sa_th_max', 'usage_scope' => 'course_definition'],
                            ['name' => 'SA-TH (Min)', 'semantic_key' => 'sa_th_min', 'usage_scope' => 'course_definition'],
                            ['name' => 'Total (TH)', 'semantic_key' => 'total_marks', 'usage_scope' => 'display_only'],
                            ['name' => 'Min (TH)', 'semantic_key' => 'sa_th_min', 'usage_scope' => 'display_only'],
                        ],
                    ],
                    [
                        'name' => 'Practical',
                        'columns' => [
                            ['name' => 'FA-PR (Max)', 'semantic_key' => 'fa_pr_max', 'usage_scope' => 'course_definition'],
                            ['name' => 'FA-PR (Min)', 'semantic_key' => 'fa_pr_min', 'usage_scope' => 'course_definition'],
                            ['name' => 'SA-PR (Max)', 'semantic_key' => 'sa_pr_max', 'usage_scope' => 'course_definition'],
                            ['name' => 'SA-PR (Min)', 'semantic_key' => 'sa_pr_min', 'usage_scope' => 'course_definition'],
                            ['name' => 'Total (PR)', 'semantic_key' => 'total_marks', 'usage_scope' => 'display_only'],
                            ['name' => 'Min (PR)', 'semantic_key' => 'sa_pr_min', 'usage_scope' => 'display_only'],
                        ],
                    ],
                    [
                        'name' => 'SLA',
                        'columns' => [
                            ['name' => 'Max (SLA)', 'semantic_key' => 'sla_max', 'usage_scope' => 'course_definition'],
                            ['name' => 'Min (SLA)', 'semantic_key' => 'sla_min', 'usage_scope' => 'course_definition'],
                        ],
                    ],
                ],
            ],
        ];

        $this->upsertLearningStructure($scheme, $learningStructure);
        $this->upsertAssessmentStructure($scheme, $assessmentStructure);
    }

    private function upsertLearningStructure(Scheme $scheme, array $structure): void
    {
        $supportsMeta = Schema::hasColumn('scheme_learning_components', 'usage_scope')
            && Schema::hasColumn('scheme_learning_components', 'semantic_key')
            && Schema::hasColumn('scheme_learning_components', 'value_kind')
            && Schema::hasColumn('scheme_learning_components', 'entry_mode')
            && Schema::hasColumn('scheme_learning_components', 'total_role');

        $displayOrder = 0;

        foreach ($structure as $l1) {
            $l1Node = $scheme->learningComponents()->updateOrCreate(
                [
                    'parent_id' => null,
                    'component_code' => Str::slug($l1['name']),
                ],
                $this->learningAttributes($l1['name'], $displayOrder++, null, $supportsMeta)
            );

            foreach ($l1['children'] ?? [] as $l2) {
                $l2Node = $scheme->learningComponents()->updateOrCreate(
                    [
                        'parent_id' => $l1Node->id,
                        'component_code' => Str::slug($l1['name'].' '.$l2['name']),
                    ],
                    $this->learningAttributes($l2['name'], $displayOrder++, $l1Node->id, $supportsMeta)
                );

                foreach ($l2['columns'] ?? [] as $column) {
                    $scheme->learningComponents()->updateOrCreate(
                        [
                            'parent_id' => $l2Node->id,
                            'component_code' => Str::slug($l1['name'].' '.$l2['name'].' '.($column['name'] ?? '')),
                        ],
                        $this->learningAttributes((string) ($column['name'] ?? ''), $displayOrder++, $l2Node->id, $supportsMeta, $column)
                    );
                }
            }
        }
    }

    private function upsertAssessmentStructure(Scheme $scheme, array $structure): void
    {
        $supportsMeta = Schema::hasColumn('scheme_assessment_components', 'value_kind')
            && Schema::hasColumn('scheme_assessment_components', 'is_input')
            && Schema::hasColumn('scheme_assessment_components', 'contributes_to_total')
            && Schema::hasColumn('scheme_assessment_components', 'semantic_key')
            && Schema::hasColumn('scheme_assessment_components', 'usage_scope')
            && Schema::hasColumn('scheme_assessment_components', 'entry_mode')
            && Schema::hasColumn('scheme_assessment_components', 'total_role');

        $displayOrder = 0;

        foreach ($structure as $l1) {
            $l1Node = $scheme->assessmentComponents()->updateOrCreate(
                [
                    'parent_id' => null,
                    'component_code' => Str::slug($l1['name']),
                ],
                $this->assessmentAttributes($l1['name'], $displayOrder++, null, $supportsMeta)
            );

            foreach ($l1['children'] ?? [] as $l2) {
                $l2Node = $scheme->assessmentComponents()->updateOrCreate(
                    [
                        'parent_id' => $l1Node->id,
                        'component_code' => Str::slug($l1['name'].' '.$l2['name']),
                    ],
                    $this->assessmentAttributes($l2['name'], $displayOrder++, $l1Node->id, $supportsMeta)
                );

                foreach ($l2['columns'] ?? [] as $column) {
                    $scheme->assessmentComponents()->updateOrCreate(
                        [
                            'parent_id' => $l2Node->id,
                            'component_code' => Str::slug($l1['name'].' '.$l2['name'].' '.($column['name'] ?? '')),
                        ],
                        $this->assessmentAttributes((string) ($column['name'] ?? ''), $displayOrder++, $l2Node->id, $supportsMeta, $column)
                    );
                }
            }
        }
    }

    private function learningAttributes(string $name, int $displayOrder, ?int $parentId, bool $supportsMeta, array $column = []): array
    {
        $attributes = [
            'component_name' => $name,
            'display_order' => $displayOrder,
        ];

        if (!$supportsMeta) {
            return $attributes;
        }

        if ($parentId === null || $column === []) {
            return $attributes + [
                'value_kind' => 'group',
                'entry_mode' => 'readonly',
                'total_role' => 'group',
                'usage_scope' => 'display_only',
                'semantic_key' => null,
            ];
        }

        $semanticKey = $column['semantic_key'] ?? null;
        $valueKind = $semanticKey === 'credits' ? 'credits' : 'hours';
        $totalRole = in_array($semanticKey, ['total_hours', 'nlh_hours'], true) ? 'derived' : ($semanticKey === 'credits' ? 'summary' : 'informational');

        return $attributes + [
            'usage_scope' => $column['usage_scope'] ?? 'syllabus',
            'semantic_key' => $semanticKey,
            'value_kind' => $valueKind,
            'entry_mode' => 'input',
            'total_role' => $totalRole,
        ];
    }

    private function assessmentAttributes(string $name, int $displayOrder, ?int $parentId, bool $supportsMeta, array $column = []): array
    {
        $attributes = [
            'component_name' => $name,
            'display_order' => $displayOrder,
        ];

        if (!$supportsMeta) {
            return $attributes;
        }

        if ($parentId === null || $column === []) {
            return $attributes + [
                'value_kind' => 'group',
                'entry_mode' => 'readonly',
                'total_role' => 'group',
                'is_input' => false,
                'contributes_to_total' => false,
                'usage_scope' => 'display_only',
                'semantic_key' => null,
            ];
        }

        $semanticKey = $column['semantic_key'] ?? null;
        $isDuration = $semanticKey === 'paper_duration';
        $isMin = $semanticKey === 'min_marks' || str_ends_with((string) $semanticKey, '_min');
        $isDerived = $semanticKey === 'total_marks';
        $usageScope = $column['usage_scope'] ?? ($isDerived ? 'display_only' : 'course_definition');

        return $attributes + [
            'usage_scope' => $usageScope,
            'semantic_key' => $semanticKey,
            'value_kind' => $isDuration ? 'duration' : 'marks',
            'entry_mode' => $isDerived ? 'readonly' : 'input',
            'total_role' => $isDuration ? 'informational' : ($isDerived ? 'derived' : ($isMin ? 'min_pass' : 'adds_to_total')),
            'is_input' => !$isDerived,
            'contributes_to_total' => !$isDuration && !$isDerived && !$isMin,
        ];
    }
}
