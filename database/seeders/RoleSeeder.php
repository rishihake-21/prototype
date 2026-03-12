<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ADMIN,
                'display_name' => 'Administrator',
                'description' => 'Full system access including user management and system configuration',
                'permissions' => [
                    'manage_users',
                    'manage_departments',
                    'view_all_syllabi',
                    'approve_syllabi',
                    'system_audit'
                ]
            ],
            [
                'name' => Role::APPROVER,
                'display_name' => 'Approver',
                'description' => 'Can review and approve syllabus submissions',
                'permissions' => [
                    'view_department_syllabi',
                    'approve_syllabi',
                    'reject_syllabi',
                    'request_changes'
                ]
            ],
            [
                'name' => Role::CREATOR,
                'display_name' => 'Creator',
                'description' => 'Can create and edit syllabi for assigned departments',
                'permissions' => [
                    'create_syllabi',
                    'edit_own_syllabi',
                    'submit_syllabi',
                    'view_department_syllabi'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
