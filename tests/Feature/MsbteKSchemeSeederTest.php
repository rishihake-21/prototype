<?php

namespace Tests\Feature;

use App\Models\Scheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MsbteKSchemeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_msbte_k_scheme_seeder_builds_expected_structure(): void
    {
        $this->seed(\Database\Seeders\MsbteKSchemeSeeder::class);

        $scheme = Scheme::where('name', 'K-Scheme')
            ->with(['levels', 'learningComponents', 'assessmentComponents'])
            ->firstOrFail();

        $this->assertSame(
            ['1', '2', '3', '4', '5', 'Au'],
            $scheme->levels->sortBy('sort_order')->pluck('level_code')->values()->all()
        );

        $this->assertDatabaseHas('scheme_learning_components', [
            'scheme_id' => $scheme->id,
            'component_name' => 'Actual Contact Hours / Week',
        ]);

        $this->assertDatabaseHas('scheme_learning_components', [
            'scheme_id' => $scheme->id,
            'component_name' => 'Credits',
            'semantic_key' => 'credits',
        ]);

        $this->assertDatabaseHas('scheme_assessment_components', [
            'scheme_id' => $scheme->id,
            'component_name' => 'FA-TH (Max)',
            'semantic_key' => 'fa_th_max',
            'usage_scope' => 'course_definition',
        ]);

        $this->assertDatabaseHas('scheme_assessment_components', [
            'scheme_id' => $scheme->id,
            'component_name' => 'FA-TH (Min)',
            'semantic_key' => 'fa_th_min',
            'usage_scope' => 'course_definition',
        ]);

        $this->assertDatabaseHas('scheme_assessment_components', [
            'scheme_id' => $scheme->id,
            'component_name' => 'Hrs',
            'semantic_key' => 'paper_duration',
            'usage_scope' => 'course_definition',
        ]);
    }
}
