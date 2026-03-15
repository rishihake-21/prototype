<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Department;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    public function index()
    {
        $hod = auth()->user();
        $department = $hod->headedDepartment;

        if (!$department) {
            // Fallback for testing: if user has HOD role but headedDepartment is null, pick first department they belong to
            $department = $hod->departments()->first();
        }

        if (!$department) {
            return redirect()->route('dashboard')->with('error', 'No department associated with your account.');
        }

        // 1. Get Courses relevant to this department
        // Courses from Programmes owned by this department
        $directCourseIds = Course::whereHas('programme', function ($q) use ($department) {
            $q->where('department_id', $department->id);
        })->pluck('id');

        // Courses mapped to this department (Common courses)
        $mappedCourseIds = DB::table('course_programme_departments')
            ->where('department_id', $department->id)
            ->pluck('course_id');

        $allCourseIds = $directCourseIds->merge($mappedCourseIds)->unique();

        $courses = Course::whereIn('id', $allCourseIds)
            ->with(['programme', 'level'])
            ->get()
            ->unique(function ($item) {
                // Determine uniqueness by code, title and credits - this hides duplicates across programmes if they are identical
                return $item->course_code . '-' . $item->course_title . '-' . $item->credits;
            })
            ->sortBy(function($course) {
                return $course->level->sort_order . $course->course_code;
            });

        // 2. Get Faculty in this department
        $faculty = $department->users()
            ->where('role', User::ROLE_FACULTY)
            ->get();

        // 3. Current Assignments (filter by current/upcoming year to reduce noise)
        $currentYear = date('Y') . '-' . (date('y') + 1);
        $assignments = CourseAssignment::where('department_id', $department->id)
            ->where('academic_year', '>=', $currentYear) // Simple filter to hide old records
            ->with(['course.programme', 'faculty', 'assigner'])
            ->latest()
            ->get();

        return view('hod.assignments.index', compact('department', 'courses', 'faculty', 'assignments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id'       => 'required|exists:courses,id',
            'faculty_user_id' => 'required|exists:users,id',
            'academic_year'   => 'required|string|max:10',
            'deadline'        => 'nullable|date|after_or_equal:today',
        ]);

        $hod = auth()->user();
        $department = $hod->headedDepartment ?: $hod->departments()->first();
        $course = Course::with(['level.structure', 'programme'])->findOrFail($request->course_id);

        $facultyAllowed = User::where('id', $request->faculty_user_id)
            ->where('role', User::ROLE_FACULTY)
            ->whereHas('departments', fn ($q) => $q->where('departments.id', $department->id))
            ->exists();

        if (!$facultyAllowed) {
            return back()->with('error', 'Selected faculty member does not belong to your department.');
        }

        $directForDepartment = (int) $course->programme->department_id === (int) $department->id;
        $mappedToDepartment = DB::table('course_programme_departments')
            ->where('course_id', $course->id)
            ->where('department_id', $department->id)
            ->exists();

        if (!$directForDepartment && !$mappedToDepartment) {
            return back()->with('error', 'This course is not available for your department.');
        }

        // Check if already assigned for this year
        $exists = CourseAssignment::where([
            'course_id'       => $request->course_id,
            'department_id'   => $department->id,
            'academic_year'   => $request->academic_year,
        ])->exists();

        if ($exists) {
            return back()->with('error', 'This course is already assigned to someone for the selected academic year.');
        }

        if ($course->course_type === Course::TYPE_ELECTIVE) {
            $electiveToComplete = (int) ($course->level?->structure?->elective_count ?? 0);
            if ($electiveToComplete <= 0) {
                return back()->with('error', 'No elective selections are configured for this level.');
            }

            $selectedElectives = CourseAssignment::where('department_id', $department->id)
                ->where('academic_year', $request->academic_year)
                ->whereHas('course', function ($q) use ($course) {
                    $q->where('level_id', $course->level_id)
                        ->where('course_type', Course::TYPE_ELECTIVE);
                })
                ->count();

            if ($selectedElectives >= $electiveToComplete) {
                return back()->with('error', "Only {$electiveToComplete} elective course(s) can be taken forward for {$course->level->level_code}.");
            }
        }

        CourseAssignment::create([
            'course_id'       => $request->course_id,
            'department_id'   => $department->id,
            'faculty_user_id' => $request->faculty_user_id,
            'assigned_by'     => $hod->id,
            'academic_year'   => $request->academic_year,
            'deadline'        => $request->deadline,
            'status'          => 'pending',
            'assigned_at'     => now(),
        ]);

        if ($course->course_type === Course::TYPE_ELECTIVE) {
            $cdcs = User::where('role', User::ROLE_CDC)->get();
            foreach ($cdcs as $cdc) {
                Notification::create([
                    'user_id' => $cdc->id,
                    'type' => Notification::TYPE_ELECTIVE_SELECTED,
                    'title' => 'HOD Selected an Elective',
                    'message' => "{$department->name} selected {$course->course_code} - {$course->course_title} for {$request->academic_year}. This elective now moves forward in the cycle.",
                    'data' => [
                        'programme_id' => $course->programme_id,
                        'course_id' => $course->id,
                        'department_id' => $department->id,
                        'academic_year' => $request->academic_year,
                    ],
                ]);
            }
        }

        return back()->with('success', 'Course assigned successfully.');
    }

    public function destroy(CourseAssignment $assignment)
    {
        // Check if HOD belongs to the department of this assignment
        $hod = auth()->user();
        if (!$hod->isAdmin() && $assignment->department_id !== ($hod->headedDepartment->id ?? null)) {
             // Second check if they are in the department
             if (!$hod->departments()->where('departments.id', $assignment->department_id)->exists()) {
                 abort(403);
             }
        }

        // Prevent revoking if already completed (unless admin)
        if ($assignment->status === 'completed' && !$hod->isAdmin()) {
            return back()->with('error', 'Cannot revoke a completed assignment. The syllabus is already approved.');
        }

        DB::transaction(function() use ($assignment) {
            // Delete associated syllabus if it's still in draft, rejected, or changes_requested mode
            foreach ($assignment->syllabi as $syllabus) {
                if ($syllabus->canBeEdited()) {
                    // Only delete drafts/rejected. If it's already approved, we keep it (but we blocked this above anyway)
                    $syllabus->forceDelete(); // Using forceDelete to truly remove it and avoid confusion
                } else {
                    // If it was submitted/under_review, we should probably null out the assignment_id
                    // but since the assignment is gone, the faculty can't "finish" it through the workflow easily.
                    $syllabus->update(['assignment_id' => null]);
                }
            }
            
            $assignment->delete();
        });

        return back()->with('success', 'Assignment revoked and associated drafts removed.');
    }
}
