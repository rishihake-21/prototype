<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\ProgrammeStructure;
use App\Models\Scheme;
use App\Models\SchemeAssessmentComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAssessmentPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_assessment_rows_are_synced_on_create_update_and_clone(): void
    {
        $user = User::create([
            'name' => 'CDC Tester',
            'email' => 'cdc@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        [$programme, $level, $leafA, $leafB] = $this->buildProgrammeContext($user);

        $storeResponse = $this->actingAs($user)->post(route('cdc.courses.store', $programme), [
            'level_id' => $level->id,
            'course_code' => '231001',
            'course_title' => 'Applied Testing',
            'course_abbr' => 'AT',
            'th_hours' => 2,
            'tu_hours' => 1,
            'pr_hours' => 2,
            'credits' => 4,
            'theory_paper_hrs' => 3,
            'course_type' => 'compulsory',
            'assessment_marks' => [
                (string) $leafA->id => '25',
                (string) $leafB->id => '75',
            ],
        ]);

        $storeResponse->assertRedirect(route('cdc.courses.index', $programme));

        $course = $programme->courses()->with('assessments')->firstOrFail();
        $this->assertSame(100, (int) $course->total_marks);
        $this->assertSame(
            [$leafA->id => 25, $leafB->id => 75],
            $course->assessments->pluck('max_marks', 'component_id')->map(fn ($marks) => (int) $marks)->all()
        );

        $updateResponse = $this->actingAs($user)->put(route('cdc.courses.update', [$programme, $course]), [
            'level_id' => $level->id,
            'course_code' => '231001',
            'course_title' => 'Applied Testing Updated',
            'course_abbr' => 'ATU',
            'th_hours' => 2,
            'tu_hours' => 1,
            'pr_hours' => 2,
            'credits' => 4,
            'theory_paper_hrs' => 3,
            'course_type' => 'compulsory',
            'assessment_marks' => [
                (string) $leafA->id => '',
                (string) $leafB->id => '60',
            ],
        ]);

        $updateResponse->assertRedirect(route('cdc.courses.index', $programme));

        $course->refresh()->load('assessments');
        $this->assertSame(60, (int) $course->total_marks);
        $this->assertSame(
            [$leafB->id => 60],
            $course->assessments->pluck('max_marks', 'component_id')->map(fn ($marks) => (int) $marks)->all()
        );

        $cloneResponse = $this->actingAs($user)->post(route('cdc.courses.clone', $course));
        $cloneResponse->assertRedirect();

        $clone = $programme->courses()
            ->where('course_code', '231001-COPY')
            ->with('assessments')
            ->firstOrFail();

        $this->assertSame(60, (int) $clone->total_marks);
        $this->assertSame(
            [$leafB->id => 60],
            $clone->assessments->pluck('max_marks', 'component_id')->map(fn ($marks) => (int) $marks)->all()
        );
    }

    public function test_minimum_assessment_fields_are_stored_without_inflating_total_marks(): void
    {
        $user = User::create([
            'name' => 'CDC Tester 4',
            'email' => 'cdc4@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        [$programme, $level, $leafA, $leafB] = $this->buildProgrammeContext($user);

        $minLeaf = SchemeAssessmentComponent::create([
            'scheme_id' => $programme->scheme_id,
            'parent_id' => $leafB->parent_id,
            'component_code' => 'MIN',
            'component_name' => 'Min (SLA)',
            'type' => 'marks',
            'semantic_key' => 'sla_min',
            'value_kind' => 'marks',
            'entry_mode' => 'input',
            'total_role' => 'min_pass',
            'is_input' => true,
            'contributes_to_total' => false,
            'display_order' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('cdc.courses.store', $programme), [
            'level_id' => $level->id,
            'course_code' => '231006',
            'course_title' => 'Assessment Split Course',
            'course_abbr' => 'ASC',
            'th_hours' => 2,
            'tu_hours' => 0,
            'pr_hours' => 2,
            'credits' => 4,
            'theory_paper_hrs' => 3,
            'course_type' => 'compulsory',
            'assessment_marks' => [
                (string) $leafA->id => '70',
                (string) $leafB->id => '30',
                (string) $minLeaf->id => '25',
            ],
        ]);

        $response->assertRedirect(route('cdc.courses.index', $programme));

        $course = $programme->courses()->with('assessments')->where('course_code', '231006')->firstOrFail();
        $this->assertSame(100, (int) $course->total_marks);

        $minAssessment = $course->assessments->firstWhere('component_id', $minLeaf->id);
        $this->assertNotNull($minAssessment);
        $this->assertSame(0, (int) $minAssessment->max_marks);
        $this->assertSame(25, (int) $minAssessment->min_marks);
    }

    public function test_course_credits_follow_scheme_limits_in_whole_number_steps(): void
    {
        $user = User::create([
            'name' => 'CDC Tester 2',
            'email' => 'cdc2@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        [$programme, $level] = $this->buildProgrammeContext($user, 30.00);

        $response = $this->actingAs($user)->post(route('cdc.courses.store', $programme), [
            'level_id' => $level->id,
            'course_code' => '231002',
            'course_title' => 'High Credit Course',
            'course_abbr' => 'HCC',
            'th_hours' => 6,
            'tu_hours' => 2,
            'pr_hours' => 4,
            'credits' => 20,
            'theory_paper_hrs' => 3,
            'course_type' => 'compulsory',
            'assessment_marks' => [],
        ]);

        $response->assertRedirect(route('cdc.courses.index', $programme));

        $course = $programme->courses()->firstOrFail();
        $this->assertSame('20.00', $course->fresh()->credits);

        $invalidResponse = $this->actingAs($user)->from(route('cdc.courses.create', $programme))->post(route('cdc.courses.store', $programme), [
            'level_id' => $level->id,
            'course_code' => '231005',
            'course_title' => 'Fractional Credit Course',
            'course_abbr' => 'FCC',
            'th_hours' => 4,
            'tu_hours' => 1,
            'pr_hours' => 2,
            'credits' => 20.5,
            'theory_paper_hrs' => 3,
            'course_type' => 'compulsory',
            'assessment_marks' => [],
        ]);

        $invalidResponse->assertRedirect(route('cdc.courses.create', $programme));
        $invalidResponse->assertSessionHasErrors(['credits']);
    }

    public function test_zero_credits_are_rejected_for_non_audit_courses_and_allowed_for_audit(): void
    {
        $user = User::create([
            'name' => 'CDC Tester 3',
            'email' => 'cdc3@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        [$programme, $level] = $this->buildProgrammeContext($user, 30.00);

        $nonAuditResponse = $this->actingAs($user)->from(route('cdc.courses.create', $programme))->post(route('cdc.courses.store', $programme), [
            'level_id' => $level->id,
            'course_code' => '231003',
            'course_title' => 'Zero Credit Theory',
            'course_abbr' => 'ZCT',
            'th_hours' => 1,
            'tu_hours' => 0,
            'pr_hours' => 0,
            'credits' => 0,
            'theory_paper_hrs' => 1,
            'course_type' => 'compulsory',
            'assessment_marks' => [],
        ]);

        $nonAuditResponse->assertRedirect(route('cdc.courses.create', $programme));
        $nonAuditResponse->assertSessionHasErrors(['credits']);
        $this->assertDatabaseCount('courses', 0);

        $auditResponse = $this->actingAs($user)->post(route('cdc.courses.store', $programme), [
            'level_id' => $level->id,
            'course_code' => '231004',
            'course_title' => 'Zero Credit Audit',
            'course_abbr' => 'ZCA',
            'th_hours' => 1,
            'tu_hours' => 0,
            'pr_hours' => 0,
            'credits' => 0,
            'theory_paper_hrs' => 1,
            'course_type' => 'audit',
            'assessment_marks' => [],
        ]);

        $auditResponse->assertRedirect(route('cdc.courses.index', $programme));
        $this->assertDatabaseHas('courses', [
            'course_code' => '231004',
            'course_type' => 'audit',
            'credits' => 0,
        ]);
    }

    /**
     * @return array{Programme, ProgrammeLevel, SchemeAssessmentComponent, SchemeAssessmentComponent}
     */
    private function buildProgrammeContext(User $user, float $totalCredits = 30.00): array
    {
        $scheme = Scheme::create([
            'name' => 'K-Scheme',
            'implemented_year' => 2023,
            'is_active' => true,
        ]);

        $root = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'component_code' => 'AS',
            'component_name' => 'Assessment Scheme',
            'type' => 'group',
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
            'display_order' => 1,
        ]);

        $theory = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $root->id,
            'component_code' => 'TH',
            'component_name' => 'Theory',
            'type' => 'group',
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
            'display_order' => 2,
        ]);

        $leafA = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $theory->id,
            'component_code' => 'FA',
            'component_name' => 'FA-TH (Max)',
            'type' => 'marks',
            'value_kind' => 'marks',
            'is_input' => true,
            'contributes_to_total' => true,
            'display_order' => 3,
        ]);

        $leafB = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $theory->id,
            'component_code' => 'SA',
            'component_name' => 'SA-TH (Max)',
            'type' => 'marks',
            'value_kind' => 'marks',
            'is_input' => true,
            'contributes_to_total' => true,
            'display_order' => 4,
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
            'level_code' => 'Level-1',
            'level_name' => 'Foundation Courses',
            'sort_order' => 1,
        ]);

        ProgrammeStructure::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'total_courses_offered' => 5,
            'courses_to_complete' => 5,
            'compulsory_count' => 5,
            'elective_count' => 0,
            'elective_offered_count' => 0,
            'th_hours' => 20,
            'tu_hours' => 10,
            'pr_hours' => 20,
            'total_hours' => 50,
            'total_credits' => $totalCredits,
            'total_marks' => 500,
        ]);

        return [$programme, $level, $leafA, $leafB];
    }
}
