<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\ProgrammeStructure;
use Illuminate\Http\Request;

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
     * Bulk-save all level rows from the Scheme at a Glance form.
     */
    public function update(Request $request, Programme $programme)
    {
        $rows = $request->input('rows', []);
        $errors = [];

        foreach ($rows as $levelId => $data) {
            $level = ProgrammeLevel::where('programme_id', $programme->id)->find($levelId);
            if (!$level) continue;

            $offered    = (int) ($data['total_courses_offered'] ?? 0);
            $toComplete = (int) ($data['courses_to_complete']   ?? 0);
            $compulsory = (int) ($data['compulsory_count']      ?? 0);
            $electiveOffered = (int) ($data['elective_offered_count'] ?? 0);
            $electiveToComplete = (int) ($data['elective_count'] ?? 0);
            $th         = (int) ($data['th_hours']              ?? 0);
            $tu         = (int) ($data['tu_hours']              ?? 0);
            $pr         = (int) ($data['pr_hours']              ?? 0);
            $credits    = (float) ($data['total_credits']       ?? 0);
            $marks      = (int) ($data['total_marks']           ?? 0);

            if ($toComplete > $offered) {
                $errors[] = "Level {$level->level_code}: 'Courses to Complete' ({$toComplete}) cannot exceed 'Total Courses Offered' ({$offered}).";
                continue;
            }

            if ($compulsory + $electiveOffered > $offered) {
                $errors[] = "Level {$level->level_code}: compulsory courses plus elective courses offered cannot exceed total courses offered.";
                continue;
            }

            if ($electiveToComplete > $electiveOffered) {
                $errors[] = "Level {$level->level_code}: elective courses to complete cannot exceed elective courses offered.";
                continue;
            }

            if ($compulsory + $electiveToComplete > $toComplete) {
                $errors[] = "Level {$level->level_code}: compulsory courses plus elective courses to complete cannot exceed courses to complete.";
                continue;
            }

            // Update ProgrammeLevel limits
            $level->update([
                'courses_limit' => $offered, // or toComplete? In reference repo they use offered.
                'th_limit'      => $th,
                'tu_limit'      => $tu,
                'pr_limit'      => $pr,
                'hours_limit'   => $th + $tu + $pr,
                'credits_limit' => $credits,
                'marks_limit'   => $marks,
                'level_name'    => $data['level_name'] ?? $level->level_name,
            ]);

            // Sync with ProgrammeStructure (for legacy/compatibility)
            ProgrammeStructure::updateOrCreate(
                ['programme_id' => $programme->id, 'level_id' => $levelId],
                [
                    'total_courses_offered' => $offered,
                    'courses_to_complete'   => $toComplete,
                    'compulsory_count'      => $compulsory,
                    'elective_offered_count'=> $electiveOffered,
                    'elective_count'        => $electiveToComplete,
                    'th_hours'              => $th,
                    'tu_hours'              => $tu,
                    'pr_hours'              => $pr,
                    'total_hours'           => $th + $tu + $pr,
                    'total_credits'         => $credits,
                    'total_marks'           => $marks,
                    'notes'                 => $data['notes'] ?? null,
                ]
            );
        }

        if ($errors) {
            return redirect()
                ->back()
                ->with('structure_errors', $errors)
                ->withInput();
        }

        return redirect()->route('cdc.courses.index', $programme)
            ->with('success', 'Master Curriculum structure saved. You can now define courses for each level.');
    }

}
