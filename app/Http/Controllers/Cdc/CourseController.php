<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\ProgrammeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function create(Programme $programme)
    {
        $programme->load('levels', 'scheme.assessmentComponents');

        return view('cdc.courses.form', [
            'programme'      => $programme,
            'course'         => null,
            'types'          => Course::types(),
            'electiveGroups' => Course::electiveGroups(),
            'departments'    => Department::orderBy('name')->get(),
        ]);
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

    public function store(Request $request, Programme $programme)
    {
        $data = $this->validateCourse($request, $programme);
        $data['total_marks'] = $this->sumAssessmentMarks($request->input('assessment_marks', []));

        $data['programme_id'] = $programme->id;
        $data['is_placeholder'] = false;

        $this->assertWithinStructureBudget($programme, (int) $data['level_id'], $data, null);

        $course = Course::create($data);

        if ($request->has('assessment_marks')) {
            $totalMarks = 0;
            
            foreach ($request->input('assessment_marks') as $compId => $marks) {
                if (is_null($marks) || trim($marks) === '') continue;
                
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
        }

        return redirect()
            ->route('cdc.courses.index', $programme)
            ->with('success', "Course '{$course->course_code}' created successfully.");
    }

    public function update(Request $request, Programme $programme, Course $course)
    {
        $data = $this->validateCourse($request, $programme, $course);
        $data['total_marks'] = $this->sumAssessmentMarks($request->input('assessment_marks', []));

        // Map course_type and is_placeholder 
        // Elective group validation might need logic later (assignElective method requested by user)
        $data['is_placeholder'] = false; 

        $this->assertWithinStructureBudget($programme, (int) $data['level_id'], $data, $course->id);

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

    public function assignElective(Request $request, Programme $programme, Course $course)
    {
        $request->validate([
            'elective_group_id' => 'required|exists:elective_groups,id',
            'course_master_id'  => 'required|exists:courses,id',
        ]);

        if (!$course->is_placeholder || $course->course_type !== 'elective') {
            return redirect()->back()->with('error', 'Only elective placeholders can be assigned.');
        }

        $groupCourse = \App\Models\ElectiveGroupCourse::where('elective_group_id', $request->elective_group_id)
            ->where('course_master_id', $request->course_master_id)
            ->first();

        if (!$groupCourse) {
            return redirect()->back()->with('error', 'Selected course is not a valid elective for this group.');
        }

        $masterCourse = Course::with('assessments')->find($request->course_master_id);

        // Validation mapping could also include passing elective group name into the course
        $electiveGroup = \App\Models\ElectiveGroup::find($request->elective_group_id);

        DB::transaction(function () use ($course, $masterCourse, $electiveGroup) {
            $course->update([
                'is_placeholder' => false,
                'linked_course_id' => $masterCourse->id,
                'course_code' => $masterCourse->course_code,
                'course_title' => $masterCourse->course_title,
                'course_abbr' => $masterCourse->course_abbr,
                'th_hours' => $masterCourse->th_hours,
                'tu_hours' => $masterCourse->tu_hours,
                'pr_hours' => $masterCourse->pr_hours,
                'total_hours' => $masterCourse->total_hours,
                'credits' => $masterCourse->credits,
                'theory_paper_hrs' => $masterCourse->theory_paper_hrs,
                'total_marks' => $masterCourse->total_marks,
                'elective_group' => $electiveGroup->name, // store group name in elective_group
            ]);

            $course->assessments()->delete();
            foreach ($masterCourse->assessments as $assessment) {
                $course->assessments()->create([
                    'component_id' => $assessment->component_id,
                    'max_marks'    => $assessment->max_marks,
                    'min_marks'    => $assessment->min_marks
                ]);
            }
        });

        return redirect()->route('cdc.courses.index', $programme)->with('success', 'Elective course assigned successfully.');
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

    private function assertWithinStructureBudget(Programme $programme, int $levelId, array $incoming, ?int $ignoreCourseId): void
    {
        $structure = ProgrammeStructure::where('programme_id', $programme->id)
            ->where('level_id', $levelId)
            ->first();

        if (! $structure) {
            throw ValidationException::withMessages([
                'level_id' => 'This level has no structure/budget defined yet. Please save "Scheme at a Glance" first.',
            ]);
        }

        // If there are still placeholders remaining after this save, we enforce "must not exceed".
        // When the level is fully filled (no placeholders), we enforce "must match exactly".
        $remainingPlaceholders = Course::where('programme_id', $programme->id)
            ->where('level_id', $levelId)
            ->whereNull('deleted_at')
            ->when($ignoreCourseId, fn ($q) => $q->where('id', '!=', $ignoreCourseId))
            ->where(function ($q) {
                $q->where('is_placeholder', true)->orWhereNull('course_code');
            })
            ->count();

        $willStillHavePlaceholders = $remainingPlaceholders > 0;

        $base = Course::where('programme_id', $programme->id)
            ->where('level_id', $levelId)
            ->whereNull('deleted_at');

        if ($ignoreCourseId) {
            $base->where('id', '!=', $ignoreCourseId);
        }

        $sumTh = (int) $base->sum('th_hours') + (int) ($incoming['th_hours'] ?? 0);
        $sumTu = (int) $base->sum('tu_hours') + (int) ($incoming['tu_hours'] ?? 0);
        $sumPr = (int) $base->sum('pr_hours') + (int) ($incoming['pr_hours'] ?? 0);
        $sumMarks = (int) $base->sum('total_marks') + (int) ($incoming['total_marks'] ?? 0);

        // Credits are decimal; compare with 2dp tolerance.
        $sumCredits = (float) $base->sum('credits') + (float) ($incoming['credits'] ?? 0);
        $creditsBudget = (float) $structure->total_credits;

        $errors = [];

        $cmp = function (int $actual, int $budget, string $fieldLabel) use (&$errors, $willStillHavePlaceholders) {
            if ($willStillHavePlaceholders) {
                if ($actual > $budget) {
                    $errors['level_id'] = "{$fieldLabel} total exceeds level budget ({$actual} > {$budget}).";
                }
            } else {
                if ($actual !== $budget) {
                    $errors['level_id'] = "{$fieldLabel} total must match level budget exactly ({$actual} != {$budget}).";
                }
            }
        };

        $cmp($sumTh, (int) $structure->th_hours, 'TH');
        $cmp($sumTu, (int) $structure->tu_hours, 'TU');
        $cmp($sumPr, (int) $structure->pr_hours, 'PR');
        $cmp($sumMarks, (int) $structure->total_marks, 'Marks');

        if ($willStillHavePlaceholders) {
            if ($sumCredits - $creditsBudget > 0.009) {
                $errors['level_id'] = "Credits total exceeds level budget (" . number_format($sumCredits, 2) . " > " . number_format($creditsBudget, 2) . ").";
            }
        } else {
            if (abs($sumCredits - $creditsBudget) > 0.009) {
                $errors['level_id'] = "Credits total must match level budget exactly (" . number_format($sumCredits, 2) . " != " . number_format($creditsBudget, 2) . ").";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function sumAssessmentMarks(array $assessmentMarks): int
    {
        return collect($assessmentMarks)
            ->filter(fn ($marks) => ! is_null($marks) && trim((string) $marks) !== '')
            ->sum(fn ($marks) => (int) $marks);
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
