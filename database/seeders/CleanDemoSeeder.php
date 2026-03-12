<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Programme;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\GlobalLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CleanDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Departments
        $deptIf = Department::updateOrCreate(['code' => 'IF'], ['name' => 'Information Technology', 'is_active' => true]);
        $deptCm = Department::updateOrCreate(['code' => 'CM'], ['name' => 'Computer Engineering', 'is_active' => true]);

        // 2. Create Admin
        User::updateOrCreate(['email' => 'admin@demo.com'], [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // 3. Create CDC In-charge
        $cdc = User::updateOrCreate(['email' => 'cdc@demo.com'], [
            'name' => 'CDC Manager',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // 4. Create HODs
        $hodIf = User::updateOrCreate(['email' => 'hod.if@demo.com'], [
            'name' => 'HOD IT',
            'password' => Hash::make('password'),
            'role' => User::ROLE_HOD,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $deptIf->update(['head_user_id' => $hodIf->id]);
        $hodIf->departments()->syncWithoutDetaching([$deptIf->id]);

        $hodCm = User::updateOrCreate(['email' => 'hod.cm@demo.com'], [
            'name' => 'HOD Computer',
            'password' => Hash::make('password'),
            'role' => User::ROLE_HOD,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $deptCm->update(['head_user_id' => $hodCm->id]);
        $hodCm->departments()->syncWithoutDetaching([$deptCm->id]);

        // 5. Create Faculty
        $facIf = User::updateOrCreate(['email' => 'fac.if@demo.com'], [
            'name' => 'Prof. Faculty IT',
            'password' => Hash::make('password'),
            'role' => User::ROLE_FACULTY,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $facIf->departments()->syncWithoutDetaching([$deptIf->id]);

        $facCm = User::updateOrCreate(['email' => 'fac.cm@demo.com'], [
            'name' => 'Prof. Faculty Computer',
            'password' => Hash::make('password'),
            'role' => User::ROLE_FACULTY,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $facCm->departments()->syncWithoutDetaching([$deptCm->id]);

        // 5.5 Create Observer
        User::updateOrCreate(['email' => 'observer@demo.com'], [
            'name' => 'Campus Observer',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OBSERVER,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // 6. Seed K-Scheme
        $scheme = \App\Models\Scheme::firstOrCreate(
        ['name' => 'K-Scheme'],
        [
            'implemented_year' => 2026,
            'description' => 'latest scheme.',
            'is_active' => true,
        ]
        );

        // Seed K-Scheme levels
        $kLevels = [
            ['level_code' => '1', 'level_name' => 'basic', 'sort_order' => 1],
            ['level_code' => '2', 'level_name' => 'Foundation', 'sort_order' => 2],
            ['level_code' => '3', 'level_name' => 'Allied Courses', 'sort_order' => 3],
            ['level_code' => '4', 'level_name' => 'Applied Courses', 'sort_order' => 4],
            ['level_code' => '5', 'level_name' => 'Diversified Courses', 'sort_order' => 5],
            ['level_code' => 'Au', 'level_name' => 'Audit Courses', 'sort_order' => 6],

        ];

        foreach ($kLevels as $levelData) {
            $scheme->levels()->firstOrCreate(
            ['level_code' => $levelData['level_code']],
            ['level_name' => $levelData['level_name'], 'sort_order' => $levelData['sort_order']]
            );
        }

        // Seed K-Scheme component hierarchy
        $schemeStructure = [
            [
                'name' => 'Learning Scheme',
                'children' => [
                    [
                        'name' => 'Actual Contact Hours / Week',
                        'children' => ['CL', 'TL', 'LL', 'Practical'],
                    ],
                    [
                        'name' => 'Self Learning (Activity / Assignment / Micro Project)',
                        'children' => [],
                    ],
                    [
                        'name' => 'Notional Learning Hours / Week',
                        'children' => [],
                    ],

                ]
            ],
            [
                'name' => 'Credits',
                'children' => [],
            ],
            [
                'name' => 'Assessment Scheme',
                'children' => [
                    [
                        'name' => 'Paper Duration',
                        'children' => [],
                    ],
                    [
                        'name' => 'Theory',
                        'children' => ['FA-TH (Max)', 'SA-TH (Max)', 'Total (TH)', 'Min (TH)'],
                    ],
                    [
                        'name' => 'Practical',
                        'children' => ['FA-PR (Max)', 'SA-PR (Max)', 'Total (PR)', 'Min (PR)'],
                    ],
                    [
                        'name' => 'SLA',
                        'children' => ['Max (SLA)', 'Min (SLA)'],
                    ],
                ]
            ]
        ];

        $displayOrder = 0;
        foreach ($schemeStructure as $level1) {
            $l1Node = $scheme->assessmentComponents()->firstOrCreate(
            ['component_code' => \Illuminate\Support\Str::slug($level1['name']), 'parent_id' => null],
            ['component_name' => $level1['name'], 'display_order' => $displayOrder++]
            );

            foreach ($level1['children'] as $level2) {
                // If it's a string, it means it's a leaf node without children in the array?
                // Wait, based on the array structure: level 2 are ALWAYS arrays with 'name' and 'children'.
                $l2Node = $scheme->assessmentComponents()->firstOrCreate(
                ['component_code' => \Illuminate\Support\Str::slug($level1['name'] . ' ' . $level2['name']), 'parent_id' => $l1Node->id],
                ['component_name' => $level2['name'], 'display_order' => $displayOrder++]
                );

                if (isset($level2['children']) && is_array($level2['children'])) {
                    foreach ($level2['children'] as $level3Name) {
                        $scheme->assessmentComponents()->firstOrCreate(
                        ['component_code' => \Illuminate\Support\Str::slug($level1['name'] . ' ' . $level2['name'] . ' ' . $level3Name), 'parent_id' => $l2Node->id],
                        ['component_name' => $level3Name, 'display_order' => $displayOrder++]
                        );
                    }
                }
            }
        }
    }

}
