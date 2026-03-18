<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\SamplePath;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamplePathControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_path_page_no_longer_shows_entry_levels_and_reads_legacy_rows(): void
    {
        $user = User::create([
            'name' => 'CDC Sample Path Tester',
            'email' => 'cdc-sample-path@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $programme = Programme::create([
            'name' => 'Diploma in Testing',
            'code' => 'DT',
            'academic_year' => '2025-26',
            'status' => Programme::STATUS_DRAFT,
            'submitted_by' => $user->id,
        ]);

        $level = ProgrammeLevel::create([
            'programme_id' => $programme->id,
            'level_code' => '1',
            'level_name' => 'Foundation Courses',
            'sort_order' => 1,
        ]);

        $course = Course::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'course_code' => '261001',
            'course_title' => 'Legacy Mapped Course',
            'course_abbr' => 'LMC',
            'th_hours' => 2,
            'tu_hours' => 0,
            'pr_hours' => 2,
            'total_hours' => 4,
            'credits' => 4,
            'theory_paper_hrs' => 3,
            'total_marks' => 100,
            'course_type' => Course::TYPE_COMPULSORY,
        ]);

        SamplePath::create([
            'programme_id' => $programme->id,
            'entry_level' => '10+',
            'term_number' => 1,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->get(route('cdc.programmes.sample-path', $programme));

        $response->assertOk();
        $response->assertDontSee('Entry Level:');
        $response->assertDontSee('10+');
        $response->assertDontSee('12+');
        $response->assertDontSee('Lateral');
        $response->assertSee('Legacy Mapped Course');
    }

    public function test_sample_path_update_replaces_old_entry_levels_with_standard_bucket(): void
    {
        $user = User::create([
            'name' => 'CDC Sample Path Saver',
            'email' => 'cdc-sample-save@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $programme = Programme::create([
            'name' => 'Diploma in Saving',
            'code' => 'DS',
            'academic_year' => '2025-26',
            'status' => Programme::STATUS_DRAFT,
            'submitted_by' => $user->id,
        ]);

        $level = ProgrammeLevel::create([
            'programme_id' => $programme->id,
            'level_code' => '1',
            'level_name' => 'Foundation Courses',
            'sort_order' => 1,
        ]);

        $course = Course::create([
            'programme_id' => $programme->id,
            'level_id' => $level->id,
            'course_code' => '261002',
            'course_title' => 'Standard Course',
            'course_abbr' => 'SC',
            'th_hours' => 2,
            'tu_hours' => 0,
            'pr_hours' => 2,
            'total_hours' => 4,
            'credits' => 4,
            'theory_paper_hrs' => 3,
            'total_marks' => 100,
            'course_type' => Course::TYPE_COMPULSORY,
        ]);

        SamplePath::create([
            'programme_id' => $programme->id,
            'entry_level' => '10+',
            'term_number' => 2,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->put(route('cdc.programmes.sample-path.update', $programme), [
            'terms' => [
                1 => [$course->id],
            ],
        ]);

        $response->assertRedirect(route('cdc.programmes.sample-path', $programme));
        $this->assertDatabaseMissing('sample_paths', [
            'programme_id' => $programme->id,
            'entry_level' => '10+',
            'course_id' => $course->id,
        ]);
        $this->assertDatabaseHas('sample_paths', [
            'programme_id' => $programme->id,
            'entry_level' => SamplePath::ENTRY_LEVEL_STANDARD,
            'term_number' => 1,
            'course_id' => $course->id,
        ]);
    }
}
