<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function index(Request $request, Programme $programme)
    {
        $query = $programme->courses()
            ->with(['level', 'departments', 'assessments'])
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
            'programme'    => $programme,
            'course'       => null,
            'types'        => Course::types(),
            'electiveGroups' => Course::electiveGroups(),
            'departments'  => Department::orderBy('name')->get(),
            'schemeRows'   => $programme->scheme?->getCourseAssessmentHeaderRows() ?? [],
            'leafCols'     => $programme->scheme?->getCourseAssessmentLeafColumns() ?? [],
            'marks'        => old('assessment_marks', []),
        ]);
    }

    public function store(Request $request, Programme $programme)
    {
        $data = $this->validateCourse($request, $programme);

        if ($request->has('assessment_marks')) {
            $this->guardAssessmentKeys($programme, $request->input('assessment_marks', []));
        }

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
        $this->assertCourseInProgramme($programme, $course);
        $programme->load('levels', 'scheme.assessmentComponents');
        $course->load('departments', 'assessments');

        $existingMarks = $course->assessments->pluck('max_marks', 'component_id')->toArray();

        return view('cdc.courses.form', [
            'programme'      => $programme,
            'course'         => $course,
            'types'          => Course::types(),
            'electiveGroups' => Course::electiveGroups(),
            'departments'    => Department::orderBy('name')->get(),
            'schemeRows'     => $programme->scheme?->getCourseAssessmentHeaderRows() ?? [],
            'leafCols'       => $programme->scheme?->getCourseAssessmentLeafColumns() ?? [],
            'marks'          => old('assessment_marks', $existingMarks),
        ]);
    }

    public function update(Request $request, Programme $programme, Course $course)
    {
        $this->assertCourseInProgramme($programme, $course);
        $data = $this->validateCourse($request, $programme, $course);

        if ($request->has('assessment_marks')) {
            $this->guardAssessmentKeys($programme, $request->input('assessment_marks', []));
        }

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
        $this->assertCourseInProgramme($programme, $course);
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

    // -- Private helpers --

    private function assertCourseInProgramme(Programme $programme, Course $course): void
    {
        abort_if((int) $course->programme_id !== (int) $programme->id, 404);
    }

    private function validateCourse(Request $request, Programme $programme, ?Course $ignore = null): array
    {
        $levelId = $request->input('level_id');
        $level = ProgrammeLevel::where('programme_id', $programme->id)->find($levelId);
        $expectedDigit = $level ? $level->sort_order : null;

        $codeRule = [
            'required',
            'string',
            'size:6',
            'regex:/^\\d{6}$/',
            Rule::unique('courses', 'course_code')
                ->ignore($ignore?->id)
                ->where(fn ($q) => $q->where('programme_id', $programme->id)->whereNull('deleted_at')),
            function (string $attribute, mixed $value, \Closure $fail) use ($expectedDigit) {
                if ($expectedDigit === null) {
                    return;
                }
                $v = (string) $value;
                if (strlen($v) !== 6) {
                    return;
                }
                $digit = substr($v, 2, 1);
                if (!ctype_digit($digit) || (int) $digit !== (int) $expectedDigit) {
                    $fail("Course code must follow YYLNNN where L matches the selected Level ({$expectedDigit}).");
                }
            },
        ];

        return $request->validate([
            'level_id'           => ['required', Rule::exists('programme_levels', 'id')->where('programme_id', $programme->id)],
            'course_code'        => $codeRule,
            'course_title'       => 'required|string|max:255',
            'course_abbr'        => 'required|string|max:20',
            'th_hours'           => 'required|integer|min:0|max:40',
            'tu_hours'           => 'required|integer|min:0|max:40',
            'pr_hours'           => 'required|integer|min:0|max:60',
            // Credits should be small. Prevent obvious data-entry mistakes (e.g. marks typed into credits).
            'credits'            => 'required|numeric|min:0|max:20',
            'theory_paper_hrs'   => 'required|numeric|min:0|max:6',
            'course_type'        => 'required|in:compulsory,elective,audit',
            'assessment_marks'   => 'nullable|array',
            'assessment_marks.*' => 'nullable|integer|min:0|max:1000',
            'elective_group'     => 'nullable|required_if:course_type,elective|string|max:50',
            'year'               => 'nullable|integer|min:1|max:5',
            'term'               => 'nullable|in:odd,even',
            'is_award'           => 'boolean',
            'is_common_course'   => 'boolean',
            'departments'        => 'nullable|array',
            'departments.*'      => 'exists:departments,id',
        ]);
    }

    /**
     * Prevent saving arbitrary component IDs via crafted requests.
     * Only allow leaf IDs that the scheme exposes for course assessment inputs.
     *
     * @param Programme $programme
     * @param array $assessmentMarks
     */
    private function guardAssessmentKeys(Programme $programme, array $assessmentMarks): void
    {
        $allowed = $programme->scheme?->getCourseAssessmentLeafColumns() ?? [];
        $allowedIds = array_fill_keys(array_map(fn ($c) => (int) $c['id'], $allowed), true);

        $bad = [];
        foreach ($assessmentMarks as $k => $_) {
            $id = is_numeric($k) ? (int) $k : null;
            if ($id === null || !isset($allowedIds[$id])) {
                $bad[] = $k;
            }
        }

        if (!empty($bad)) {
            throw ValidationException::withMessages([
                'assessment_marks' => 'Invalid assessment columns provided (scheme mismatch).',
            ]);
        }
    }
    public function apiShow(Request $request, $code)
    {
        $query = Course::with(['programme', 'level', 'departments', 'assessments.component'])
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

        foreach ($this->mapLegacyExamSchemeFields($course) as $k => $v) {
            $course->setAttribute($k, $v);
        }

        return response()->json($course);
    }

    /**
     * @return array<string, int|null>
     */
    private function mapLegacyExamSchemeFields(Course $course): array
    {
        $out = [
            'test_max_marks' => null,   // FA-TH (Max)
            'theory_max_marks' => null, // SA-TH (Max)
            'pr_max_marks' => null,     // SA-PR (Max)
            'or_max_marks' => null,     // OR (Max)
            'tw_max_marks' => null,     // TW / SLA (Max)
        ];

        foreach ($course->assessments as $a) {
            $name = strtolower(trim((string) ($a->component?->component_name ?? '')));
            $max = is_numeric($a->max_marks) ? (int) $a->max_marks : null;
            if ($max === null) {
                continue;
            }

            if (($out['test_max_marks'] === null) && str_contains($name, 'fa-th') && str_contains($name, 'max')) {
                $out['test_max_marks'] = $max;
                continue;
            }
            if (($out['theory_max_marks'] === null) && str_contains($name, 'sa-th') && str_contains($name, 'max')) {
                $out['theory_max_marks'] = $max;
                continue;
            }
            if (($out['pr_max_marks'] === null) && str_contains($name, 'sa-pr') && str_contains($name, 'max')) {
                $out['pr_max_marks'] = $max;
                continue;
            }
            if (($out['or_max_marks'] === null) && (str_contains($name, 'or') || str_contains($name, 'oral')) && str_contains($name, 'max')) {
                $out['or_max_marks'] = $max;
                continue;
            }
            if (($out['tw_max_marks'] === null) && (str_contains($name, 'tw') || str_contains($name, 'sla')) && str_contains($name, 'max')) {
                $out['tw_max_marks'] = $max;
                continue;
            }
        }

        return $out;
    }
}
