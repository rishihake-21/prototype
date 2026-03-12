<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request, Programme $programme)
    {
        $query = $programme->courses()
            ->with(['level', 'departments'])
            ->withTrashed();  // allow seeing soft-deleted for management

        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        if ($request->filled('show_deleted')) {
            $query->onlyTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        $courses = $query->orderBy('level_id')->orderBy('course_code')->paginate(30);

        $programme->load('levels');

        return view('cdc.courses.index', compact('programme', 'courses'));
    }

    /**
     * Show a grid for bulk editing all courses in a specific level.
     */
    public function bulkEdit(Programme $programme, ProgrammeLevel $level)
    {
        $courses = Course::where('level_id', $level->id)
            ->where('programme_id', $programme->id)
            ->with('assessments')
            ->orderBy('course_code')
            ->get();

        $electiveGroups = Course::electiveGroups();

        // Fetch the "Budget" from the Scheme at a Glance (ProgrammeStructure)
        $budget = \App\Models\ProgrammeStructure::where('programme_id', $programme->id)
            ->where('level_id', $level->id)
            ->first();
            
        $schemeRows = $programme->scheme?->getHeaderRows() ?? [];
        $leafCols   = $programme->scheme?->getLeafColumns() ?? [];

        return view('cdc.courses.bulk-edit', compact('programme', 'level', 'courses', 'electiveGroups', 'budget', 'schemeRows', 'leafCols'));
    }

    /**
     * Bulk update or create courses for a specific level.
     */
    public function bulkUpdate(Request $request, Programme $programme, ProgrammeLevel $level)
    {
        $rows = $request->input('rows', []);
        
        DB::transaction(function () use ($rows, $programme, $level) {
            $totalTH = 0;
            $totalTU = 0;
            $totalPR = 0;
            $totalCredits = 0;
            $totalMarks = 0;
            $courseCount = 0;

            foreach ($rows as $id => $data) {
                // Skip empty rows (unintentional extra rows or invalid data)
                if (empty($data['course_code'])) {
                    continue;
                }

                // Optional: Validate course code format if needed, but keeping it flexible
                if (empty($data['course_code'])) {
                    throw new \Exception("Course code is required for all rows.");
                }

                // Sum up for strict validation
                $th = (int) ($data['th_hours'] ?? 0);
                $tu = (int) ($data['tu_hours'] ?? 0);
                $pr = (int) ($data['pr_hours'] ?? 0);
                $credits = (float) ($data['credits'] ?? 0);
                
                // My total marks is sum of all marks components mapped in bulk edit 
                $marks = (int) ($data['total_marks'] ?? 0);

                if (($data['course_type'] ?? 'compulsory') !== 'audit') {
                    $totalTH += $th;
                    $totalTU += $tu;
                    $totalPR += $pr;
                    $totalCredits += $credits;
                    $totalMarks += $marks;
                    $courseCount++;
                }

                $course = null;
                if (is_numeric($id) && $id > 0) {
                    $course = Course::find($id);
                }

                if (!$course) {
                    $course = new Course();
                    $course->programme_id = $programme->id;
                    $course->level_id     = $level->id;
                }

                $course->fill([
                    'course_code'       => $data['course_code'],
                    'course_title'      => $data['course_title'] ?? 'Untitled',
                    'course_abbr'       => $data['course_abbr'] ?? 'UNT',
                    'th_hours'          => $th,
                    'tu_hours'          => $tu,
                    'pr_hours'          => $pr,
                    'credits'           => $credits,
                    'theory_paper_hrs'  => (int) ($data['theory_paper_hrs'] ?? 0),
                    'total_marks'       => $marks,
                    'course_type'       => $data['course_type'] ?? 'compulsory',
                    'year'              => $data['year'] ?? null,
                    'term'              => $data['term'] ?? null,
                    'is_award'          => isset($data['is_award']),
                    'elective_group'    => ($data['course_type'] === 'elective') ? ($data['elective_group'] ?? null) : null,
                ]);
                
                $course->save();
            }

            // ----------------------------
            // STRICT MASTER STRUCTURE VALIDATION
            // ----------------------------
            if (
                $totalTH      != $level->th_limit ||
                $totalTU      != $level->tu_limit ||
                $totalPR      != $level->pr_limit ||
                $totalCredits != $level->credits_limit ||
                $totalMarks   != $level->marks_limit ||
                $courseCount  != $level->courses_limit
            ) {
                $msg = "Strict Structure Mismatch for {$level->level_code}! ".
                       "Target vs Current: ".
                       "Courses({$level->courses_limit}/{$courseCount}), ".
                       "TH({$level->th_limit}/{$totalTH}), ".
                       "TU({$level->tu_limit}/{$totalTU}), ".
                       "PR({$level->pr_limit}/{$totalPR}), ".
                       "Credits({$level->credits_limit}/{$totalCredits}), ".
                       "Marks({$level->marks_limit}/{$totalMarks}).";
                throw new \Exception($msg);
            }

            // Sync legacy table
            $structure = \App\Models\ProgrammeStructure::firstOrNew([
                'programme_id' => $programme->id,
                'level_id'     => $level->id
            ]);
            $structure->fill([
                'total_courses_offered' => $courseCount,
                'th_hours'              => $totalTH,
                'tu_hours'              => $totalTU,
                'pr_hours'              => $totalPR,
                'total_hours'           => $totalTH + $totalTU + $totalPR,
                'total_credits'         => $totalCredits,
                'total_marks'           => $totalMarks,
            ])->save();
        });

        return redirect()->route('cdc.courses.index', $programme)
            ->with('success', "Updated courses and synchronized Scheme at a Glance for {$level->level_code} successfully.");
    }

    public function create(Programme $programme)
    {
        $programme->load('levels', 'scheme.assessmentComponents');

        return view('cdc.courses.form', [
            'programme'    => $programme,
            'course'       => null,
            'types'        => Course::types(),
            'electiveGroups' => Course::electiveGroups(),
            'departments'  => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Programme $programme)
    {
        $data = $this->validateCourse($request, $programme);

        $course = $programme->courses()->create($data);

        if ($request->has('assessment_marks')) {
            $totalMarks = 0;
            foreach ($request->input('assessment_marks') as $compId => $marks) {
                if (is_null($marks) || trim($marks) === '') continue; // Skip empty entries
                
                $course->assessments()->create([
                    'component_id' => $compId,
                    'max_marks'    => $marks,
                    'min_marks'    => 0 // Default or handle later
                ]);
                $totalMarks += (int) $marks;
            }
            // Update the stored total marks
            $course->update(['total_marks' => $totalMarks]);
        }

        // Sync departments for common courses
        if ($course->is_common_course && $request->filled('departments')) {
            $course->departments()->sync($request->input('departments'));
        }

        return redirect()
            ->route('cdc.courses.index', $programme)
            ->with('success', "Course '{$course->course_code}' created successfully.");
    }

    public function edit(Programme $programme, Course $course)
    {
        $programme->load('levels', 'scheme.assessmentComponents');
        $course->load('departments', 'assessments');

        return view('cdc.courses.form', [
            'programme'      => $programme,
            'course'         => $course,
            'types'          => Course::types(),
            'electiveGroups' => Course::electiveGroups(),
            'departments'    => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Programme $programme, Course $course)
    {
        $data = $this->validateCourse($request, $programme, $course);

        $course->update($data);

        if ($request->has('assessment_marks')) {
            $course->assessments()->delete();
            $totalMarks = 0;
            
            foreach ($request->input('assessment_marks') as $compId => $marks) {
                if (is_null($marks) || trim($marks) === '') continue; // Skip empty entries
                
                $course->assessments()->create([
                    'component_id' => $compId,
                    'max_marks'    => $marks,
                    'min_marks'    => 0
                ]);
                $totalMarks += (int) $marks;
            }
            $course->update(['total_marks' => $totalMarks]);
        }

        // Sync departments
        if ($course->is_common_course && $request->filled('departments')) {
            $course->departments()->sync($request->input('departments'));
        } else {
            $course->departments()->detach();
        }

        return redirect()
            ->route('cdc.courses.index', $programme)
            ->with('success', "Course '{$course->course_code}' updated successfully.");
    }

    public function destroy(Programme $programme, Course $course)
    {
        $code = $course->course_code;
        $course->delete();  // Soft delete

        return redirect()
            ->route('cdc.courses.index', $programme)
            ->with('success', "Course '{$code}' removed.");
    }

    public function clone(Request $request, Course $course)
    {
        $clone = $course->replicate();
        $clone->course_code  = $course->course_code . '-COPY';
        $clone->course_title = $course->course_title . ' (Copy)';
        $clone->deleted_at   = null;
        $clone->save();

        // Copy department links
        if ($course->is_common_course) {
            $clone->departments()->sync($course->departments->pluck('id'));
        }

        return redirect()
            ->route('cdc.courses.edit', [$course->programme_id, $clone])
            ->with('success', 'Course cloned. Please update the course code and title.');
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function validateCourse(Request $request, Programme $programme, ?Course $ignore = null): array
    {
        $levelId = $request->input('level_id');
        $level = ProgrammeLevel::find($levelId);
        $expectedDigit = $level ? $level->sort_order : null;

        $codeRule = [
            'required',
            'string',
            'max:20', // Increased max length for flexibility
            'unique:courses,course_code,' . ($ignore?->id ?? 'NULL') . ',id,programme_id,' . $programme->id . ',deleted_at,NULL',
        ];

        return $request->validate([
            'level_id'           => 'required|exists:programme_levels,id',
            'course_code'        => $codeRule,
            'course_title'       => 'required|string|max:255',
            'course_abbr'        => 'required|string|max:20',
            'th_hours'           => 'required|integer|min:0',
            'tu_hours'           => 'required|integer|min:0',
            'pr_hours'           => 'required|integer|min:0',
            'credits'            => 'required|numeric|min:0',
            'theory_paper_hrs'   => 'required|numeric|min:0',
            'course_type'        => 'required|in:compulsory,elective,audit',
            'assessment_marks'   => 'nullable|array',
            'assessment_marks.*' => 'nullable|integer|min:0',
            'elective_group'     => 'nullable|required_if:course_type,elective|string|max:50',
            'year'               => 'nullable|integer|min:1|max:5',
            'term'               => 'nullable|in:odd,even',
            'is_award'           => 'boolean',
            'is_common_course'   => 'boolean',
            'departments'        => 'nullable|array',
            'departments.*'      => 'exists:departments,id',
        ]);
    }
    public function apiShow(Request $request, $code)
    {
        $query = Course::with(['programme', 'level', 'departments'])
            ->where('course_code', $code);

        // Filter by programme code if provided
        if ($request->filled('programme')) {
            $query->whereHas('programme', function($q) use ($request) {
                $q->where('code', $request->programme)
                  ->orWhere('name', $request->programme);
            });
        }

        // Filter by academic year if provided
        if ($request->filled('year')) {
            $query->whereHas('programme', function($q) use ($request) {
                $q->where('academic_year', $request->year);
            });
        }

        $course = $query->first();

        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        return response()->json($course);
    }
}
