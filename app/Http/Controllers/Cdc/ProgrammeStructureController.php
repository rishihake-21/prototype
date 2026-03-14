<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\ProgrammeStructure;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgrammeStructureController extends Controller
{
    /**
     * Show the Scheme at a Glance table.
     */
    public function index(Programme $programme)
    {
        $programme->load(['levels' => function ($q) {
            $q->with('structure')->orderBy('sort_order');
        }]);

        return view('cdc.structure.index', compact('programme'));
    }

    /**
     * Bulk-save all level rows from the Scheme at a Glance form and generate placeholders.
     */
    public function update(Request $request, Programme $programme)
    {
        $rows = $request->input('rows', []);
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $levelId => $data) {
                $level = ProgrammeLevel::where('programme_id', $programme->id)->find($levelId);
                if (!$level) continue;

                $toComplete = (int) ($data['courses_to_complete']   ?? 0);
                $compulsory = (int) ($data['compulsory_count']      ?? 0);
                $elective   = (int) ($data['elective_count']        ?? 0);
                $audit      = (int) ($data['audit_count']           ?? 0);
                $offered    = $compulsory + $elective + $audit; // computed: avoid mismatched input
                $th         = (int) ($data['th_hours']              ?? 0);
                $tu         = (int) ($data['tu_hours']              ?? 0);
                $pr         = (int) ($data['pr_hours']              ?? 0);
                $credits    = (float) ($data['total_credits']       ?? 0);
                $marks      = (int) ($data['total_marks']           ?? 0);

                if ($toComplete > $offered) {
                    $errors[] = "Level {$level->level_code}: 'Courses to Complete' ({$toComplete}) cannot exceed 'Total Courses Offered' ({$offered}).";
                    continue;
                }

                // Update ProgrammeLevel limits
                $level->update([
                    'courses_limit' => $offered,
                    'th_limit'      => $th,
                    'tu_limit'      => $tu,
                    'pr_limit'      => $pr,
                    'hours_limit'   => $th + $tu + $pr,
                    'credits_limit' => $credits,
                    'marks_limit'   => $marks,
                    'level_name'    => $data['level_name'] ?? $level->level_name,
                ]);

                // Sync with ProgrammeStructure
                ProgrammeStructure::updateOrCreate(
                    ['programme_id' => $programme->id, 'level_id' => $levelId],
                    [
                        'total_courses_offered' => $offered,
                        'courses_to_complete'   => $toComplete,
                        'compulsory_count'      => $compulsory,
                        'elective_count'        => $elective,
                        'audit_count'           => $audit,
                        'th_hours'              => $th,
                        'tu_hours'              => $tu,
                        'pr_hours'              => $pr,
                        'total_hours'           => $th + $tu + $pr,
                        'total_credits'         => $credits,
                        'total_marks'           => $marks,
                        'notes'                 => $data['notes'] ?? null,
                    ]
                );

                // -----------------------------------------------------------------
                // Auto-create missing placeholder courses based on defined rules
                // -----------------------------------------------------------------
                // We map the UI counts to the desired types. 
                // In a fuller implementation, the UI would send the exact rules per type.
                // Here, we derive the requirements based on compulsory and elective counts submitted.
                
                $rules = [
                    ['course_type' => 'compulsory', 'offered' => $compulsory],
                    ['course_type' => 'elective', 'offered' => $elective],
                    ['course_type' => 'audit', 'offered' => $audit],
                ];

                foreach ($rules as $rule) {
                    $currentCourseCount = Course::where('programme_id', $programme->id)
                        ->where('level_id', $level->id)
                        ->where('course_type', $rule['course_type'])
                        ->count();

                    $targetOffered = $rule['offered'];

                    if ($targetOffered > $currentCourseCount) {
                        // Create missing placeholders
                        $missing = $targetOffered - $currentCourseCount;
                        for ($i = 0; $i < $missing; $i++) {
                            Course::create([
                                'programme_id'   => $programme->id,
                                'level_id'       => $level->id,
                                'course_type'    => $rule['course_type'],
                                'is_placeholder' => true,
                                'course_code'    => null,
                                'course_title'   => null,
                                'course_abbr'    => null,
                            ]);
                        }
                    } elseif ($targetOffered < $currentCourseCount) {
                        // Determine how many placeholders we need to remove
                        $excess = $currentCourseCount - $targetOffered;
                        
                        // Get strictly "placeholder" courses first
                        $emptyCourses = Course::where('programme_id', $programme->id)
                            ->where('level_id', $level->id)
                            ->where('course_type', $rule['course_type'])
                            ->where('is_placeholder', true)
                            ->get();

                        if ($emptyCourses->count() >= $excess) {
                            // Safe to delete empty placeholders
                            foreach ($emptyCourses->take($excess) as $c) {
                                $c->forceDelete(); 
                            }
                        } else {
                            $errors[] = "Level {$level->level_code} ({$rule['course_type']}): Cannot reduce courses to {$targetOffered}. Some courses are already defined. Please remove them manually.";
                        }
                    }
                }
            }

            if ($errors) {
                DB::rollBack();
                return redirect()->back()->with('error', implode(' | ', $errors))->withInput();
            }

            DB::commit();
            return redirect()->route('cdc.courses.index', $programme)
                ->with('success', 'Master Curriculum structure saved. Placeholders generated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating structure: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Re-calculate structure totals from courses and return JSON.
     * Used by the "Calculate from Courses" button.
     */
    public function calculate(Programme $programme)
    {
        $programme->load(['levels.structure']);

        $result = [];

        foreach ($programme->levels as $level) {
            $struct = $level->structure ?? new ProgrammeStructure([
                'programme_id' => $programme->id,
                'level_id'     => $level->id,
            ]);

            $result[$level->id] = $struct->calculateFromCourses();
        }

        return response()->json($result);
    }
}
