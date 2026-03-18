<?php

namespace Tests\Feature;

use App\Models\AwardClassCourse;
use App\Models\Course;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\ProgrammeStructure;
use App\Models\SamplePath;
use App\Models\Scheme;
use App\Models\SchemeAssessmentComponent;
use App\Models\SchemeLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeBookletTest extends TestCase
{
    use RefreshDatabase;

    public function test_booklet_includes_structure_sample_path_and_award_class_sections(): void
    {
        $user = User::create([
            'name' => 'CDC Booklet Tester',
            'email' => 'cdc-booklet@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $scheme = Scheme::create([
            'name' => 'Booklet Scheme',
            'implemented_year' => 2026,
            'is_active' => true,
        ]);

        SchemeLevel::create([
            'scheme_id' => $scheme->id,
            'level_code' => '1',
            'level_name' => 'Foundation Courses',
            'sort_order' => 1,
        ]);

        $root = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'component_code' => 'assessment-scheme',
            'component_name' => 'Assessment Scheme',
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
            'display_order' => 1,
        ]);

        $theory = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $root->id,
            'component_code' => 'assessment-scheme-theory',
            'component_name' => 'Theory',
            'value_kind' => 'group',
            'is_input' => false,
            'contributes_to_total' => false,
            'display_order' => 2,
        ]);

        $faTh = SchemeAssessmentComponent::create([
            'scheme_id' => $scheme->id,
            'parent_id' => $theory->id,
            'component_code' => 'fa-th-max',
            'component_name' => 'FA-TH (Max)',
            'semantic_key' => 'fa_th_max',
            'usage_scope' => 'course_definition',
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
            'level_name' => 'Foundation Courses',
            'sort_order' => 1,
        ]);

        ProgrammeStructure::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'total_courses_offered' => 2,
            'courses_to_complete' => 2,
            'compulsory_count' => 1,
            'elective_offered_count' => 1,
            'elective_count' => 1,
            'th_hours' => 4,
            'tu_hours' => 1,
            'pr_hours' => 2,
            'total_hours' => 7,
            'total_credits' => 5,
            'total_marks' => 200,
        ]);

        $compulsory = Course::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'course_code' => '261001',
            'course_title' => 'Core Subject',
            'course_abbr' => 'CS',
            'th_hours' => 3,
            'tu_hours' => 1,
            'pr_hours' => 0,
            'total_hours' => 4,
            'credits' => 3,
            'theory_paper_hrs' => 3,
            'total_marks' => 100,
            'course_type' => Course::TYPE_COMPULSORY,
        ]);

        $elective = Course::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'course_code' => '261002',
            'course_title' => 'Elective Subject',
            'course_abbr' => 'ES',
            'th_hours' => 1,
            'tu_hours' => 0,
            'pr_hours' => 2,
            'total_hours' => 3,
            'credits' => 2,
            'theory_paper_hrs' => 2,
            'total_marks' => 100,
            'course_type' => Course::TYPE_ELECTIVE,
            'elective_group' => 'Elective I',
        ]);

        $compulsory->assessments()->create([
            'component_id' => $faTh->id,
            'max_marks' => 20,
            'min_marks' => 0,
        ]);

        $elective->assessments()->create([
            'component_id' => $faTh->id,
            'max_marks' => 30,
            'min_marks' => 0,
        ]);

        SamplePath::create([
            'programme_id' => $programme->id,
            'entry_level' => '10+',
            'term_number' => 1,
            'course_id' => $compulsory->id,
        ]);

        SamplePath::create([
            'programme_id' => $programme->id,
            'entry_level' => '10+',
            'term_number' => 1,
            'course_id' => $elective->id,
        ]);

        AwardClassCourse::create([
            'programme_id' => $programme->id,
            'course_id' => $compulsory->id,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('cdc.programmes.booklet', $programme));

        $response->assertOk();
        $response->assertSee('Scheme at a Glance');
        $response->assertSee('Sample Path');
        $response->assertSee('Programme Structure');
        $response->assertSee('Examination Scheme');
        $response->assertSee('Courses for Award of Class');
        $response->assertSee('Nature of Course');
        $response->assertSee('FA-TH (Max)');
        $response->assertSee('20');
        $response->assertSee('30');
        $response->assertSee('Core Subject');
        $response->assertSee('Elective Subject');
        $response->assertDontSee('Entry Level:');
        $response->assertSee('Programme - DIPLOMA IN TESTING');
    }

    public function test_booklet_renders_audit_level_as_audit_courses_only(): void
    {
        $user = User::create([
            'name' => 'CDC Audit Booklet Tester',
            'email' => 'cdc-audit-booklet@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $scheme = Scheme::create([
            'name' => 'Audit Booklet Scheme',
            'implemented_year' => 2026,
            'is_active' => true,
        ]);

        SchemeLevel::create([
            'scheme_id' => $scheme->id,
            'level_code' => 'Au',
            'level_name' => 'Audit Courses',
            'sort_order' => 6,
        ]);

        $programme = Programme::create([
            'name' => 'Diploma in Audit Testing',
            'code' => 'DAT',
            'academic_year' => '2025-26',
            'status' => Programme::STATUS_DRAFT,
            'submitted_by' => $user->id,
            'scheme_id' => $scheme->id,
        ]);

        $auditLevel = ProgrammeLevel::create([
            'programme_id' => $programme->id,
            'level_code' => 'Au',
            'level_name' => 'Audit Courses',
            'sort_order' => 6,
        ]);

        ProgrammeStructure::create([
            'programme_id' => $programme->id,
            'level_id' => $auditLevel->id,
            'total_courses_offered' => 1,
            'courses_to_complete' => 1,
            'compulsory_count' => 0,
            'elective_offered_count' => 0,
            'elective_count' => 0,
            'th_hours' => 1,
            'tu_hours' => 0,
            'pr_hours' => 1,
            'total_hours' => 2,
            'total_credits' => 0,
            'total_marks' => 0,
        ]);

        Course::create([
            'programme_id' => $programme->id,
            'level_id' => $auditLevel->id,
            'course_code' => '26A001',
            'course_title' => 'Audit Seminar',
            'course_abbr' => 'AS',
            'th_hours' => 1,
            'tu_hours' => 0,
            'pr_hours' => 1,
            'total_hours' => 2,
            'credits' => 0,
            'theory_paper_hrs' => 0,
            'total_marks' => 0,
            'course_type' => Course::TYPE_AUDIT,
        ]);

        $response = $this->actingAs($user)->get(route('cdc.programmes.booklet', $programme));

        $response->assertOk();
        $response->assertSee('Audit Courses');
        $response->assertDontSee('No compulsory courses defined for this level.');
        $response->assertDontSee('No elective courses defined for this level.');
        $response->assertDontSee('No audit courses defined for this level.');
        $response->assertSee('Audit Seminar');
    }

    public function test_booklet_award_class_shows_elective_group_breaks(): void
    {
        $user = User::create([
            'name' => 'CDC Award Group Tester',
            'email' => 'cdc-award-group@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $scheme = Scheme::create([
            'name' => 'Award Group Scheme',
            'implemented_year' => 2026,
            'is_active' => true,
        ]);

        SchemeLevel::create([
            'scheme_id' => $scheme->id,
            'level_code' => '5',
            'level_name' => 'Diversified Courses',
            'sort_order' => 5,
        ]);

        $programme = Programme::create([
            'name' => 'Diploma in Group Testing',
            'code' => 'DGT',
            'academic_year' => '2025-26',
            'status' => Programme::STATUS_DRAFT,
            'submitted_by' => $user->id,
            'scheme_id' => $scheme->id,
        ]);

        $level = ProgrammeLevel::create([
            'programme_id' => $programme->id,
            'level_code' => '5',
            'level_name' => 'Diversified Courses',
            'sort_order' => 5,
        ]);

        ProgrammeStructure::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'total_courses_offered' => 2,
            'courses_to_complete' => 2,
            'compulsory_count' => 0,
            'elective_offered_count' => 2,
            'elective_count' => 2,
            'th_hours' => 6,
            'tu_hours' => 0,
            'pr_hours' => 4,
            'total_hours' => 10,
            'total_credits' => 8,
            'total_marks' => 300,
        ]);

        $electiveA = Course::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'course_code' => '265001',
            'course_title' => 'Elective Alpha',
            'course_abbr' => 'EA',
            'th_hours' => 3,
            'tu_hours' => 0,
            'pr_hours' => 2,
            'total_hours' => 5,
            'credits' => 4,
            'theory_paper_hrs' => 3,
            'total_marks' => 150,
            'course_type' => Course::TYPE_ELECTIVE,
            'elective_group' => 'Elective III',
        ]);

        $electiveB = Course::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'course_code' => '265002',
            'course_title' => 'Elective Beta',
            'course_abbr' => 'EB',
            'th_hours' => 3,
            'tu_hours' => 0,
            'pr_hours' => 2,
            'total_hours' => 5,
            'credits' => 4,
            'theory_paper_hrs' => 3,
            'total_marks' => 150,
            'course_type' => Course::TYPE_ELECTIVE,
            'elective_group' => 'Elective IV',
        ]);

        AwardClassCourse::create([
            'programme_id' => $programme->id,
            'course_id' => $electiveA->id,
            'sort_order' => 0,
        ]);

        AwardClassCourse::create([
            'programme_id' => $programme->id,
            'course_id' => $electiveB->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('cdc.programmes.booklet', $programme));

        $response->assertOk();
        $response->assertSee('Elective III : Selected elective subjects for Award of Class');
        $response->assertSee('Elective IV : Selected elective subjects for Award of Class');
        $response->assertSee('Elective Alpha');
        $response->assertSee('Elective Beta');
    }
}
