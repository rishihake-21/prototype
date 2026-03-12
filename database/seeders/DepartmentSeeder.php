<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Computer Science', 'code' => 'CS'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Software Engineering', 'code' => 'SE'],
            ['name' => 'Data Science', 'code' => 'DS'],
            ['name' => 'Cybersecurity', 'code' => 'CYBER'],
            ['name' => 'Business Administration', 'code' => 'BA'],
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Physics', 'code' => 'PHYS'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
