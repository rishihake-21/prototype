<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\Scheme;
use App\Models\SchemeAssessmentComponent;
use App\Models\SchemeLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemeUpdatePreservesAssessmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheme_update_preserves_assessment_rows_for_non_destructive_updates(): void
    {
        [$user, $scheme, $schemeLevel, $course, $leaf] = $this->buildSchemeCourseContext();

        $response = $this->actingAs($user)->put(route('cdc.schemes.update', $scheme), [
            'name' => $scheme->name,
            'implemented_year' => $scheme->implemented_year,
            'description' => 'Updated without removing assessment columns',
            'is_active' => '1',
            'levels' => [
                [
                    'id' => $schemeLevel->id,
                    'level_code' => $schemeLevel->level_code,
                    'level_name' => $schemeLevel->level_name,
                    'sort_order' => $schemeLevel->sort_order,
                ],
            ],
            'learning_structure' => [],
            'assessment_structure' => [
                [
                    'name' => 'Assessment Scheme',
                    'children' => [
                        [
                            'name' => 'Theory',
                            'columns' => ['FA-TH (Max)', 'SA-TH (Max)'],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('cdc.schemes.index'));

        $this->assertDatabaseHas('course_assessments', [
            'course_id' => $course->id,
            'component_id' => $leaf->id,
            'max_marks' => 30,
        ]);
    }

    public function test_scheme_update_blocks_removal_of_used_assessment_component(): void
    {
        [$user, $scheme, $schemeLevel, $course, $leaf] = $this->buildSchemeCourseContext();

        $response = $this->actingAs($user)
            ->from(route('cdc.schemes.edit', $scheme))
            ->put(route('cdc.schemes.update', $scheme), [
                'name' => $scheme->name,
                'implemented_year' => $scheme->implemented_year,
                'description' => $scheme->description,
                'is_active' => '1',
                'levels' => [
                    [
                        'id' => $schemeLevel->id,
                        'level_code' => $schemeLevel->level_code,
                        'level_name' => $schemeLevel->level_name,
                        'sort_order' => $schemeLevel->sort_order,
                    ],
                ],
                'learning_structure' => [],
                'assessment_structure' => [
                    [
                        'name' => 'Assessment Scheme',
                        'children' => [
                            [
                                'name' => 'Theory',
                                'columns' => [],
                            ],
                        ],
                    ],
                ],
            ]);

        $response->assertRedirect(route('cdc.schemes.edit', $scheme));
        $response->assertSessionHasErrors(['assessment_structure']);

        $this->assertDatabaseHas('scheme_assessment_components', [
            'id' => $leaf->id,
            'component_name' => 'FA-TH (Max)',
        ]);

        $this->assertDatabaseHas('course_assessments', [
            'course_id' => $course->id,
            'component_id' => $leaf->id,
            'max_marks' => 30,
        ]);
    }

    public function test_scheme_metadata_controls_which_columns_course_definition_uses(): void
    {
        $user = User::create([
            'name' => 'CDC Metadata Tester',
            'email' => 'cdc-metadata@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $response = $this->actingAs($user)->post(route('cdc.schemes.store'), [
            'name' => 'Metadata Driven Scheme',
            'implemented_year' => 2026,
            'description' => 'Assessment roles are explicit',
            'is_active' => '1',
            'levels' => [
                [
                    'level_code' => '1',
                    'level_name' => 'Basic',
                    'sort_order' => 1,
                ],
            ],
            'learning_structure' => [],
            'assessment_structure' => [
                [
                    'name' => 'Assessment Scheme',
                    'children' => [
                        [
                            'name' => 'Theory',
                            'columns' => [
                                [
                                    'name' => 'Continuous Internal',
                                    'semantic_key' => 'fa_th_max',
                                    'usage_scope' => 'course_definition',
                                ],
                                [
                                    'name' => 'Continuous Internal Min',
                                    'semantic_key' => 'fa_th_min',
                                    'usage_scope' => 'course_definition',
                                ],
                                [
                                    'name' => 'Hidden Total',
                                    'semantic_key' => 'total_marks',
                                    'usage_scope' => 'display_only',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('cdc.schemes.index'));

        $scheme = Scheme::where('name', 'Metadata Driven Scheme')->firstOrFail();
        $leafs = $scheme->fresh()->getCourseAssessmentLeafColumns();

        $this->assertCount(2, $leafs);
        $this->assertSame('Continuous Internal', $leafs[0]['name']);
        $this->assertSame('fa_th_max', $leafs[0]['semantic_key']);
        $this->assertSame('Continuous Internal Min', $leafs[1]['name']);
        $this->assertSame('fa_th_min', $leafs[1]['semantic_key']);

        $this->assertDatabaseHas('scheme_assessment_components', [
            'scheme_id' => $scheme->id,
            'component_name' => 'Continuous Internal',
            'semantic_key' => 'fa_th_max',
            'usage_scope' => 'course_definition',
        ]);

        $this->assertDatabaseHas('scheme_assessment_components', [
            'scheme_id' => $scheme->id,
            'component_name' => 'Continuous Internal Min',
            'semantic_key' => 'fa_th_min',
            'usage_scope' => 'course_definition',
        ]);

        $this->assertDatabaseHas('scheme_assessment_components', [
            'scheme_id' => $scheme->id,
            'component_name' => 'Hidden Total',
            'semantic_key' => 'total_marks',
            'usage_scope' => 'display_only',
        ]);
    }

    public function test_unused_scheme_can_be_deleted_cleanly(): void
    {
        $user = User::create([
            'name' => 'CDC Delete Tester',
            'email' => 'cdc-delete@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $scheme = Scheme::create([
            'name' => 'Disposable Scheme',
            'implemented_year' => 2026,
            'description' => 'Delete me',
            'is_active' => true,
        ]);

        SchemeLevel::create([
            'scheme_id' => $scheme->id,
            'level_code' => '1',
            'level_name' => 'Basic',
            'sort_order' => 1,
        ]);

        $root = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'component_code' => 'assessment-scheme',
            'component_name' => 'Assessment Scheme',
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
            'display_order' => 0,
        ]);

        SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $root->id,
            'component_code' => 'assessment-scheme-theory',
            'component_name' => 'Theory',
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
            'display_order' => 1,
        ]);

        $response = $this->actingAs($user)->delete(route('cdc.schemes.destroy', $scheme));

        $response->assertRedirect(route('cdc.schemes.index'));
        $this->assertDatabaseMissing('schemes', ['id' => $scheme->id]);
        $this->assertDatabaseMissing('scheme_levels', ['scheme_id' => $scheme->id]);
        $this->assertDatabaseMissing('scheme_assessment_components', ['scheme_id' => $scheme->id]);
    }

    /**
     * @return array{User, Scheme, SchemeLevel, Course, SchemeAssessmentComponent}
     */
    private function buildSchemeCourseContext(): array
    {
        $user = User::create([
            'name' => 'CDC Scheme Tester',
            'email' => 'cdc-scheme@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $scheme = Scheme::create([
            'name' => 'Protected Scheme',
            'implemented_year' => 2026,
            'description' => 'Scheme under test',
            'is_active' => true,
        ]);

        $schemeLevel = SchemeLevel::create([
            'scheme_id' => $scheme->id,
            'level_code' => '1',
            'level_name' => 'Basic',
            'sort_order' => 1,
        ]);

        $root = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'component_code' => 'assessment-scheme',
            'component_name' => 'Assessment Scheme',
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
            'display_order' => 0,
        ]);

        $theory = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $root->id,
            'component_code' => 'assessment-scheme-theory',
            'component_name' => 'Theory',
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
            'display_order' => 1,
        ]);

        $leaf = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $theory->id,
            'component_code' => 'assessment-scheme-theory-fa-th-max',
            'component_name' => 'FA-TH (Max)',
            'value_kind' => 'marks',
            'is_input' => true,
            'contributes_to_total' => true,
            'display_order' => 2,
        ]);

        SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $theory->id,
            'component_code' => 'assessment-scheme-theory-sa-th-max',
            'component_name' => 'SA-TH (Max)',
            'value_kind' => 'marks',
            'is_input' => true,
            'contributes_to_total' => true,
            'display_order' => 3,
        ]);

        $programme = Programme::create([
            'name' => 'Diploma in Testing',
            'code' => 'DT',
            'academic_year' => '2025-26',
            'status' => Programme::STATUS_DRAFT,
            'submitted_by' => $user->id,
            'scheme_id' => $scheme->id,
        ]);

        $level = ProgrammeLevel::create([
            'programme_id' => $programme->id,
            'level_code' => '1',
            'level_name' => 'Basic',
            'sort_order' => 1,
        ]);

        $course = Course::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'course_code' => '261001',
            'course_title' => 'Protected Course',
            'course_abbr' => 'PC',
            'th_hours' => 2,
            'tu_hours' => 0,
            'pr_hours' => 2,
            'total_hours' => 4,
            'credits' => 2,
            'theory_paper_hrs' => 3,
            'total_marks' => 30,
            'course_type' => Course::TYPE_COMPULSORY,
            'is_common_course' => false,
            'is_award' => false,
        ]);

        $course->assessments()->create([
            'component_id' => $leaf->id,
            'max_marks' => 30,
            'min_marks' => 0,
        ]);

        return [$user, $scheme, $schemeLevel, $course, $leaf];
    }
}
