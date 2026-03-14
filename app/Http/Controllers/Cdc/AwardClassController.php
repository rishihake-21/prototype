<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\AwardClassCourse;
use App\Models\Programme;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class AwardClassController extends Controller
{
    /**
     * Show the Award Class course management page.
     */
    public function index(Programme $programme)
    {
        $courses = $programme->courses()
            ->with('level')
            ->whereNull('deleted_at')
            ->where('is_placeholder', false)
            ->orderBy('level_id')
            ->orderBy('course_code')
            ->get();

        $assigned = $programme->awardClassCourses()
            ->with(['course.level'])
            ->get()
            ->pluck('course_id')
            ->toArray();

        return view('cdc.award_class.index', compact('programme', 'courses', 'assigned'));
    }

    /**
     * Update the list of courses for Award of Class.
     */
    public function update(Request $request, Programme $programme)
    {
        $request->validate([
            'course_ids'   => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        $courseIds = $request->input('course_ids', []);

        // Delete current and re-insert or use sync logic with AwardClassCourse table
        DB::transaction(function () use ($programme, $courseIds) {
            $programme->awardClassCourses()->delete();

            foreach ($courseIds as $index => $courseId) {
                AwardClassCourse::create([
                    'programme_id' => $programme->id,
                    'course_id'    => (int) $courseId,
                    'sort_order'   => $index,
                ]);
            }
        });

        return redirect()
            ->route('cdc.programmes.award-class', $programme)
            ->with('success', 'Courses for Award of Class updated successfully.');
    }
}
