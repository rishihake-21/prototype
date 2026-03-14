<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Level;

class LevelSeeder extends Seeder
{
    public function run()
    {
        $levels = [
            'Foundation Courses',
            'Basic Technology Courses',
            'Allied Courses',
            'Applied Technology Courses',
            'Diversified Courses',
            'Audit Courses'
        ];

        foreach ($levels as $level) {
            Level::create([
                'level_name' => $level
            ]);
        }
    }
}