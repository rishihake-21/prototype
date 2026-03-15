<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\User;
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

        $programme->load(['levels' => function ($q) {
            $q->with('structure')->orderBy('sort_order');
        }]);

        return view('cdc.courses.index', compact('programme', 'courses'));
    }

    public function create(Request $request, Programme $programme)
    {
        $programme->load([
            'levels' => function ($q) {
                $q->with('structure')->orderBy('sort_order');
            },
            'scheme.assessmentComponents',
        ]);

        $selectedLevelId = old('level_id', $request->query('level_id', $programme->levels->first()?->id));
        $levelStats = $this->buildLevelStats($programme);

        return view('cdc.courses.form', [
            'programme'    => $programme,
            'course'       => null,
            'types'        => Course::types(),
            'electiveGroups' => Course::electiveGroups(),
            'departments'  => Department::orderBy('name')->get(),
            'schemeRows'   => $programme->scheme?->getCourseAssessmentHeaderRows() ?? [],
            'leafCols'     => $programme->scheme?->getCourseAssessmentLeafColumns() ?? [],
            'marks'        => old('assessment_marks', []),
            'levelStats'   => $levelStats,
            'selectedLevelId' => $selectedLevelId,
        ]);
    }

    public function store(Request $request, Programme $programme)
    {
        $data = $this->validateCourse($request, $programme);
        $data['elective_group'] = ($data['course_type'] ?? null) === Course::TYPE_ELECTIVE
            ? ($data['elective_group'] ?? null)
            : null;
        $this->validateLevelCapacity($programme, $data);

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

        $this->notifyHodsForElectivePool($course, 'created');

        return redirect()
            ->route('cdc.courses.index', $programme)
            ->with('success', "Course '{$course->course_code}' created successfully.");
    }

    public function edit(Programme $programme, Course $course)
    {
        $this->assertCourseInProgramme($programme, $course);
        $programme->load([
            'levels' => function ($q) {
                $q->with('structure')->orderBy('sort_order');
            },
            'scheme.assessmentComponents',
        ]);
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
            'levelStats'     => $this->buildLevelStats($programme, $course),
            'selectedLevelId' => old('level_id', $course->level_id),
        ]);
    }

    public function update(Request $request, Programme $programme, Course $course)
    {
        $this->assertCourseInProgramme($programme, $course);
        $data = $this->validateCourse($request, $programme, $course);
        $data['elective_group'] = ($data['course_type'] ?? null) === Course::TYPE_ELECTIVE
            ? ($data['elective_group'] ?? null)
            : null;
        $this->validateLevelCapacity($programme, $data, $course);

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

        $this->notifyHodsForElectivePool($course, 'updated');

        return redirect()
            ->route('cdc.courses.index', $programme)
            ->with('success', "Course '{$course->course_code}' updated successfully.");
    }

    public function destroy(Programme $programme, Course $course)
    {
        $this->assertCourseInProgramme($programme, $course);
        $wasElective = $course->course_type === Course::TYPE_ELECTIVE;
        $code = $course->course_code;
        $course->delete();  // Soft delete

        if ($wasElective) {
            $this->notifyHodsForElectivePool($course, 'removed');
        }

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
            'departments'        => 'nullable|required_if:is_common_course,1|array|min:1',
            'departments.*'      => 'exists:departments,id',
        ], [
            'departments.required_if' => 'Select at least one department for a common course.',
            'departments.min' => 'Select at least one department for a common course.',
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

    /**
     * @return array<int, array<string, int|string|null>>
     */
    private function buildLevelStats(Programme $programme, ?Course $ignore = null): array
    {
        $courses = Course::query()
            ->where('programme_id', $programme->id)
            ->whereNull('deleted_at')
            ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore->id))
            ->get([
                'id',
                'level_id',
                'course_type',
                'th_hours',
                'tu_hours',
                'pr_hours',
                'credits',
                'total_marks',
            ]);

        $grouped = $courses->groupBy('level_id');
        $stats = [];

        foreach ($programme->levels as $level) {
            $structure = $level->structure;
            $rows = $grouped->get($level->id, collect());
            $defined = $rows->count();
            $compulsoryDefined = $rows->where('course_type', Course::TYPE_COMPULSORY)->count();
            $electiveDefined = $rows->where('course_type', Course::TYPE_ELECTIVE)->count();
            $offered = (int) ($structure?->total_courses_offered ?? $level->courses_limit ?? 0);
            $compulsoryLimit = (int) ($structure?->compulsory_count ?? 0);
            $electiveOfferedLimit = (int) ($structure?->elective_offered_count ?? $structure?->elective_count ?? 0);
            $electiveToCompleteLimit = (int) ($structure?->elective_count ?? 0);
            $thUsed = (int) $rows->sum('th_hours');
            $tuUsed = (int) $rows->sum('tu_hours');
            $prUsed = (int) $rows->sum('pr_hours');
            $hoursUsed = $thUsed + $tuUsed + $prUsed;
            $creditsUsed = (float) $rows->sum('credits');
            $marksUsed = (int) $rows->sum('total_marks');

            $stats[$level->id] = [
                'offered' => $offered,
                'defined' => $defined,
                'remaining' => max($offered - $defined, 0),
                'compulsory_limit' => $compulsoryLimit,
                'compulsory_defined' => $compulsoryDefined,
                'compulsory_remaining' => max($compulsoryLimit - $compulsoryDefined, 0),
                'elective_limit' => $electiveOfferedLimit,
                'elective_offered_limit' => $electiveOfferedLimit,
                'elective_defined' => $electiveDefined,
                'elective_remaining' => max($electiveOfferedLimit - $electiveDefined, 0),
                'elective_to_complete_limit' => $electiveToCompleteLimit,
                'th_limit' => (int) ($structure?->th_hours ?? $level->th_limit ?? 0),
                'th_used' => $thUsed,
                'tu_limit' => (int) ($structure?->tu_hours ?? $level->tu_limit ?? 0),
                'tu_used' => $tuUsed,
                'pr_limit' => (int) ($structure?->pr_hours ?? $level->pr_limit ?? 0),
                'pr_used' => $prUsed,
                'hours_limit' => (int) ($structure?->total_hours ?? $level->hours_limit ?? 0),
                'hours_used' => $hoursUsed,
                'credits_limit' => (float) ($structure?->total_credits ?? $level->credits_limit ?? 0),
                'credits_used' => $creditsUsed,
                'marks_limit' => (int) ($structure?->total_marks ?? $level->marks_limit ?? 0),
                'marks_used' => $marksUsed,
                'level_code' => $level->level_code,
            ];
        }

        return $stats;
    }

    private function validateLevelCapacity(Programme $programme, array $data, ?Course $ignore = null): void
    {
        $level = $programme->levels()
            ->with('structure')
            ->findOrFail($data['level_id']);

        $stats = $this->buildLevelStats($programme, $ignore)[$level->id] ?? null;
        if (!$stats) {
            return;
        }

        $errors = [];
        $offered = (int) $stats['offered'];
        $courseMarks = isset($data['assessment_marks']) && is_array($data['assessment_marks'])
            ? $this->sumAssessmentMarks($data['assessment_marks'])
            : 0;
        $newTotalHours = (int) $data['th_hours'] + (int) $data['tu_hours'] + (int) $data['pr_hours'];

        if ($offered <= 0) {
            $errors['level_id'] = "No course slots are configured for {$level->level_code}. Update Scheme at a Glance first.";
        } elseif ((int) $stats['defined'] >= $offered) {
            $errors['level_id'] = "All {$offered} course slots for {$level->level_code} are already defined.";
        }

        if (($data['course_type'] ?? null) === Course::TYPE_COMPULSORY) {
            $limit = (int) $stats['compulsory_limit'];
            if ($limit > 0 && (int) $stats['compulsory_defined'] >= $limit) {
                $errors['course_type'] = "Compulsory course limit reached for {$level->level_code}.";
            }
        }

        if (($data['course_type'] ?? null) === Course::TYPE_ELECTIVE) {
            $limit = (int) $stats['elective_limit'];
            if ($limit > 0 && (int) $stats['elective_defined'] >= $limit) {
                $errors['course_type'] = "Elective course limit reached for {$level->level_code}.";
            }
        }

        if (($data['th_hours'] + $data['tu_hours'] + $data['pr_hours']) <= 0) {
            $errors['th_hours'] = 'At least one of TH, TU, or PR must be greater than 0.';
        }

        $thLimit = (int) $stats['th_limit'];
        if ($thLimit > 0 && ((int) $stats['th_used'] + (int) $data['th_hours']) > $thLimit) {
            $errors['th_hours'] = "TH limit exceeded for {$level->level_code}. Allowed total is {$thLimit}.";
        }

        $tuLimit = (int) $stats['tu_limit'];
        if ($tuLimit > 0 && ((int) $stats['tu_used'] + (int) $data['tu_hours']) > $tuLimit) {
            $errors['tu_hours'] = "TU limit exceeded for {$level->level_code}. Allowed total is {$tuLimit}.";
        }

        $prLimit = (int) $stats['pr_limit'];
        if ($prLimit > 0 && ((int) $stats['pr_used'] + (int) $data['pr_hours']) > $prLimit) {
            $errors['pr_hours'] = "PR limit exceeded for {$level->level_code}. Allowed total is {$prLimit}.";
        }

        $hoursLimit = (int) $stats['hours_limit'];
        if ($hoursLimit > 0 && ((int) $stats['hours_used'] + $newTotalHours) > $hoursLimit) {
            $errors['th_hours'] = "Total hours limit exceeded for {$level->level_code}. Allowed total is {$hoursLimit}.";
        }

        $creditsLimit = (float) $stats['credits_limit'];
        if ($creditsLimit > 0 && ((float) $stats['credits_used'] + (float) $data['credits']) > $creditsLimit) {
            $errors['credits'] = "Credits limit exceeded for {$level->level_code}. Allowed total is {$creditsLimit}.";
        }

        $marksLimit = (int) $stats['marks_limit'];
        if ($marksLimit > 0 && ((int) $stats['marks_used'] + $courseMarks) > $marksLimit) {
            $errors['assessment_marks'] = "Total marks limit exceeded for {$level->level_code}. Allowed total is {$marksLimit}.";
        }

        if ((float) $data['credits'] > 0 && $newTotalHours === 0) {
            $errors['credits'] = 'Credits cannot be greater than 0 when TH, TU, and PR are all 0.';
        }

        if (($data['course_type'] ?? null) === Course::TYPE_AUDIT) {
            if ((float) $data['credits'] !== 0.0) {
                $errors['credits'] = 'Audit courses must have 0 credits.';
            }
            if ($courseMarks > 0) {
                $errors['assessment_marks'] = 'Audit courses should not have assessment marks.';
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param array<mixed> $assessmentMarks
     */
    private function sumAssessmentMarks(array $assessmentMarks): int
    {
        $total = 0;
        foreach ($assessmentMarks as $marks) {
            if ($marks === null || $marks === '') {
                continue;
            }

            $total += (int) $marks;
        }

        return $total;
    }

    private function notifyHodsForElectivePool(Course $course, string $action): void
    {
        if ($course->course_type !== Course::TYPE_ELECTIVE) {
            return;
        }

        $course->loadMissing(['programme.department', 'level.structure', 'departments']);

        $departmentIds = collect([$course->programme?->department_id])
            ->merge($course->departments->pluck('id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($departmentIds)) {
            return;
        }

        $hods = User::query()
            ->where('role', User::ROLE_HOD)
            ->whereHas('departments', function ($q) use ($departmentIds) {
                $q->whereIn('departments.id', $departmentIds);
            })
            ->get();

        if ($hods->isEmpty()) {
            return;
        }

        $electiveToComplete = (int) ($course->level?->structure?->elective_count ?? 0);
        $verb = match ($action) {
            'created' => 'added to',
            'removed' => 'removed from',
            default => 'updated in',
        };

        foreach ($hods as $hod) {
            Notification::create([
                'user_id' => $hod->id,
                'type' => Notification::TYPE_ELECTIVE_POOL_UPDATED,
                'title' => 'Elective Pool Updated by CDC',
                'message' => "Course {$course->course_code} was {$verb} the elective pool for {$course->programme->name} ({$course->level->level_code}). HOD can take forward up to {$electiveToComplete} elective(s).",
                'data' => [
                    'programme_id' => $course->programme_id,
                    'course_id' => $course->id,
                    'level_id' => $course->level_id,
                    'action' => $action,
                ],
            ]);
        }
    }

    public function apiShow(Request $request, $code)
    {
        $query = Course::with(['programme.scheme', 'level', 'departments', 'assessments.component'])
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

        $course->setAttribute('assessment_scheme_rows', $course->programme?->scheme?->getCourseAssessmentHeaderRows() ?? []);
        $course->setAttribute('assessment_scheme_leaf_columns', $course->programme?->scheme?->getCourseAssessmentLeafColumns() ?? []);
        $course->setAttribute('assessment_scheme_values', $course->assessments->map(function ($assessment) {
            return [
                'component_id' => (int) $assessment->component_id,
                'component_name' => $assessment->component?->component_name,
                'max_marks' => is_numeric($assessment->max_marks) ? (int) $assessment->max_marks : null,
                'min_marks' => is_numeric($assessment->min_marks) ? (int) $assessment->min_marks : null,
            ];
        })->values());

        return response()->json($course);
    }

    /**
     * @return array<string, int|null>
     */
    private function mapLegacyExamSchemeFields(Course $course): array
    {
        $out = [
            'test_max_marks' => null,   // FA-TH (Max)
            'test_min_marks' => null,   // FA-TH (Min)
            'theory_max_marks' => null, // SA-TH (Max)
            'theory_min_marks' => null, // SA-TH (Min)
            'pr_max_marks' => null,     // SA-PR (Max)
            'pr_min_marks' => null,     // SA-PR (Min)
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
            if (($out['test_min_marks'] === null) && str_contains($name, 'fa-th') && str_contains($name, 'min')) {
                $out['test_min_marks'] = is_numeric($a->min_marks) ? (int) $a->min_marks : $max;
                continue;
            }
            if (($out['theory_max_marks'] === null) && str_contains($name, 'sa-th') && str_contains($name, 'max')) {
                $out['theory_max_marks'] = $max;
                continue;
            }
            if (($out['theory_min_marks'] === null) && str_contains($name, 'sa-th') && str_contains($name, 'min')) {
                $out['theory_min_marks'] = is_numeric($a->min_marks) ? (int) $a->min_marks : $max;
                continue;
            }
            if (($out['pr_max_marks'] === null) && str_contains($name, 'sa-pr') && str_contains($name, 'max')) {
                $out['pr_max_marks'] = $max;
                continue;
            }
            if (($out['pr_min_marks'] === null) && str_contains($name, 'sa-pr') && str_contains($name, 'min')) {
                $out['pr_min_marks'] = is_numeric($a->min_marks) ? (int) $a->min_marks : $max;
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
