<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\Scheme;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
        $this->ensureAssessmentStructureForCourseEntry($programme);
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

        $assessmentMarks = $this->normalizeAssessmentMarks($data['assessment_marks'] ?? null);
        if ($assessmentMarks !== null) {
            $this->guardAssessmentKeys($programme, $assessmentMarks);
        }

        $course = DB::transaction(function () use ($programme, $data, $assessmentMarks) {
            $course = $programme->courses()->create($data);
            $this->syncAssessmentMarks($course, $assessmentMarks);

            return $course;
        });

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
        $this->ensureAssessmentStructureForCourseEntry($programme);
        $programme->load([
            'levels' => function ($q) {
                $q->with('structure')->orderBy('sort_order');
            },
            'scheme.assessmentComponents',
        ]);
        $course->load('departments', 'assessments');

        $leafCols = $programme->scheme?->getCourseAssessmentLeafColumns() ?? [];
        $existingMarks = $this->buildAssessmentFormValues($course, $leafCols);

        return view('cdc.courses.form', [
            'programme'      => $programme,
            'course'         => $course,
            'types'          => Course::types(),
            'electiveGroups' => Course::electiveGroups(),
            'departments'    => Department::orderBy('name')->get(),
            'schemeRows'     => $programme->scheme?->getCourseAssessmentHeaderRows() ?? [],
            'leafCols'       => $leafCols,
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

        $assessmentMarks = $this->normalizeAssessmentMarks($data['assessment_marks'] ?? null);
        if ($assessmentMarks !== null) {
            $this->guardAssessmentKeys($programme, $assessmentMarks);
        }

        DB::transaction(function () use ($course, $data, $assessmentMarks) {
            $course->update($data);
            $this->syncAssessmentMarks($course, $assessmentMarks);
        });

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
        $clone = DB::transaction(function () use ($course) {
            $clone = $course->replicate();
            $clone->course_code  = $course->course_code . '-COPY';
            $clone->course_title = $course->course_title . ' (Copy)';
            $clone->deleted_at   = null;
            $clone->save();

            $course->loadMissing('assessments');
            $payload = $course->assessments->map(function ($assessment) {
                return [
                    'component_id' => $assessment->component_id,
                    'max_marks' => is_numeric($assessment->max_marks) ? (int) $assessment->max_marks : 0,
                    'min_marks' => is_numeric($assessment->min_marks) ? (int) $assessment->min_marks : 0,
                ];
            })->all();

            if (!empty($payload)) {
                $clone->assessments()->createMany($payload);
            }
            $clone->update(['total_marks' => (int) $course->total_marks]);

            if ($course->is_common_course) {
                $clone->departments()->sync($course->departments->pluck('id'));
            }

            return $clone;
        });

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
            // Credits are controlled by Scheme at a Glance; use whole-number steps.
            'credits'            => 'required|numeric|min:0|multiple_of:1',
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
            ? $this->sumAssessmentMarks($data['assessment_marks'], $this->getAssessmentComponentMeta($programme))
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

        $creditsLimit = round((float) $stats['credits_limit'], 2);
        $creditsUsed = round((float) $stats['credits_used'], 2);
        $newCreditsTotal = round($creditsUsed + (float) $data['credits'], 2);
        if ($creditsLimit > 0 && $newCreditsTotal > $creditsLimit) {
            $errors['credits'] = "Credits limit exceeded for {$level->level_code}. Allowed total is ".number_format($creditsLimit, 2).".";
        }

        $marksLimit = (int) $stats['marks_limit'];
        if ($marksLimit > 0 && ((int) $stats['marks_used'] + $courseMarks) > $marksLimit) {
            $errors['assessment_marks'] = "Total marks limit exceeded for {$level->level_code}. Allowed total is {$marksLimit}.";
        }

        if ((float) $data['credits'] > 0 && $newTotalHours === 0) {
            $errors['credits'] = 'Credits cannot be greater than 0 when TH, TU, and PR are all 0.';
        }

        if (($data['course_type'] ?? null) !== Course::TYPE_AUDIT && (float) $data['credits'] <= 0.0) {
            $errors['credits'] = 'Credits must be greater than 0 for non-audit courses.';
        }

        if (($data['course_type'] ?? null) === Course::TYPE_AUDIT) {
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
    private function sumAssessmentMarks(array $assessmentMarks, array $componentMeta = []): int
    {
        $total = 0;
        foreach ($assessmentMarks as $componentId => $marks) {
            if ($marks === null || $marks === '') {
                continue;
            }

            if (!$this->componentCountsTowardTotal($componentMeta[(int) $componentId] ?? null)) {
                continue;
            }

            $total += (int) $marks;
        }

        return $total;
    }

    /**
     * @param array<mixed>|null $assessmentMarks
     * @return array<int, int>|null
     */
    private function normalizeAssessmentMarks(?array $assessmentMarks): ?array
    {
        if ($assessmentMarks === null) {
            return null;
        }

        $normalized = [];
        foreach ($assessmentMarks as $componentId => $marks) {
            if (!is_numeric($componentId)) {
                continue;
            }

            if ($marks === null || trim((string) $marks) === '') {
                continue;
            }

            $normalized[(int) $componentId] = (int) $marks;
        }

        return $normalized;
    }

    /**
     * @param array<int, int>|null $assessmentMarks
     */
    private function syncAssessmentMarks(Course $course, ?array $assessmentMarks): void
    {
        $course->assessments()->delete();

        if (empty($assessmentMarks)) {
            $course->update(['total_marks' => 0]);
            return;
        }

        $payload = [];
        $componentMeta = $this->getAssessmentComponentMeta($course->programme);
        $totalMarks = 0;

        foreach ($assessmentMarks as $componentId => $marks) {
            $meta = $componentMeta[(int) $componentId] ?? null;
            if (!$this->shouldPersistAssessmentComponent($meta)) {
                continue;
            }

            $isMinPass = $this->componentStoresMinMarks($meta);
            if ($this->componentCountsTowardTotal($meta)) {
                $totalMarks += (int) $marks;
            }

            $payload[] = [
                'component_id' => $componentId,
                'max_marks' => $isMinPass ? 0 : $marks,
                'min_marks' => $isMinPass ? $marks : 0,
            ];
        }

        if (!empty($payload)) {
            $course->assessments()->createMany($payload);
        }

        $course->update(['total_marks' => $totalMarks]);
    }

    /**
     * @param array<int, array<string, mixed>> $leafCols
     * @return array<int, int>
     */
    private function buildAssessmentFormValues(Course $course, array $leafCols): array
    {
        $course->loadMissing('assessments');
        $byComponent = $course->assessments->keyBy('component_id');
        $values = [];

        foreach ($leafCols as $leaf) {
            $assessment = $byComponent->get((int) ($leaf['id'] ?? 0));
            if (!$assessment) {
                continue;
            }

            if ($this->componentStoresMinMarks($leaf)) {
                $value = is_numeric($assessment->min_marks)
                    ? (int) $assessment->min_marks
                    : (is_numeric($assessment->max_marks) ? (int) $assessment->max_marks : null);
            } else {
                $value = is_numeric($assessment->max_marks)
                    ? (int) $assessment->max_marks
                    : (is_numeric($assessment->min_marks) ? (int) $assessment->min_marks : null);
            }

            if ($value !== null) {
                $values[(int) $leaf['id']] = $value;
            }
        }

        return $values;
    }

    /**
     * @return array<int, array{semantic_key:?string,total_role:?string,entry_mode:?string,contributes_to_total:bool}>
     */
    private function getAssessmentComponentMeta(Programme $programme): array
    {
        $programme->loadMissing('scheme.assessmentComponents');

        $components = $programme->scheme?->assessmentComponents ?? collect();
        $meta = [];

        foreach ($components as $component) {
            $meta[(int) $component->id] = [
                'semantic_key' => $component->semantic_key,
                'total_role' => $component->total_role,
                'entry_mode' => $component->entry_mode,
                'contributes_to_total' => (bool) $component->contributes_to_total,
            ];
        }

        return $meta;
    }

    /**
     * @param array<string, mixed>|null $meta
     */
    private function componentCountsTowardTotal(?array $meta): bool
    {
        if ($meta === null) {
            return true;
        }

        $semanticKey = (string) ($meta['semantic_key'] ?? '');
        $totalRole = (string) ($meta['total_role'] ?? '');

        if ($semanticKey === 'total_marks' || str_ends_with($semanticKey, '_min') || $semanticKey === 'min_marks') {
            return false;
        }

        if (in_array($totalRole, ['derived', 'min_pass', 'informational'], true)) {
            return false;
        }

        return (bool) ($meta['contributes_to_total'] ?? true);
    }

    /**
     * @param array<string, mixed>|null $meta
     */
    private function componentStoresMinMarks(?array $meta): bool
    {
        $semanticKey = (string) ($meta['semantic_key'] ?? '');
        $totalRole = (string) ($meta['total_role'] ?? '');

        return $totalRole === 'min_pass'
            || $semanticKey === 'min_marks'
            || str_ends_with($semanticKey, '_min');
    }

    /**
     * @param array<string, mixed>|null $meta
     */
    private function shouldPersistAssessmentComponent(?array $meta): bool
    {
        if ($meta === null) {
            return true;
        }

        $semanticKey = (string) ($meta['semantic_key'] ?? '');
        $entryMode = (string) ($meta['entry_mode'] ?? '');
        $totalRole = (string) ($meta['total_role'] ?? '');

        if ($semanticKey === 'total_marks' || $totalRole === 'derived' || $entryMode === 'readonly') {
            return false;
        }

        return true;
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

    private function ensureAssessmentStructureForCourseEntry(Programme $programme): void
    {
        $programme->loadMissing('scheme.assessmentComponents');
        $scheme = $programme->scheme;

        if (!$scheme) {
            return;
        }

        $leafColumns = $scheme->getCourseAssessmentLeafColumns();
        if (!empty($leafColumns)) {
            return;
        }

        $supportsMeta = Schema::hasColumn('scheme_assessment_components', 'value_kind')
            && Schema::hasColumn('scheme_assessment_components', 'is_input')
            && Schema::hasColumn('scheme_assessment_components', 'contributes_to_total')
            && Schema::hasColumn('scheme_assessment_components', 'semantic_key')
            && Schema::hasColumn('scheme_assessment_components', 'usage_scope')
            && Schema::hasColumn('scheme_assessment_components', 'entry_mode')
            && Schema::hasColumn('scheme_assessment_components', 'total_role');

        $displayOrder = 0;

        $assessmentRoot = $scheme->assessmentComponents()->updateOrCreate(
            ['parent_id' => null, 'component_code' => 'assessment-scheme'],
            $this->assessmentComponentAttributes('Assessment Scheme', $displayOrder++, null, $supportsMeta)
        );

        $groups = [
            'Theory' => [
                ['name' => 'FA-TH (Max)', 'semantic_key' => 'fa_th_max', 'usage_scope' => 'course_definition', 'total_role' => 'adds_to_total', 'is_input' => true, 'contributes_to_total' => true],
                ['name' => 'FA-TH (Min)', 'semantic_key' => 'fa_th_min', 'usage_scope' => 'course_definition', 'total_role' => 'min_pass', 'is_input' => true, 'contributes_to_total' => false],
                ['name' => 'SA-TH (Max)', 'semantic_key' => 'sa_th_max', 'usage_scope' => 'course_definition', 'total_role' => 'adds_to_total', 'is_input' => true, 'contributes_to_total' => true],
                ['name' => 'SA-TH (Min)', 'semantic_key' => 'sa_th_min', 'usage_scope' => 'course_definition', 'total_role' => 'min_pass', 'is_input' => true, 'contributes_to_total' => false],
            ],
            'Practical' => [
                ['name' => 'FA-PR (Max)', 'semantic_key' => 'fa_pr_max', 'usage_scope' => 'course_definition', 'total_role' => 'adds_to_total', 'is_input' => true, 'contributes_to_total' => true],
                ['name' => 'FA-PR (Min)', 'semantic_key' => 'fa_pr_min', 'usage_scope' => 'course_definition', 'total_role' => 'min_pass', 'is_input' => true, 'contributes_to_total' => false],
                ['name' => 'SA-PR (Max)', 'semantic_key' => 'sa_pr_max', 'usage_scope' => 'course_definition', 'total_role' => 'adds_to_total', 'is_input' => true, 'contributes_to_total' => true],
                ['name' => 'SA-PR (Min)', 'semantic_key' => 'sa_pr_min', 'usage_scope' => 'course_definition', 'total_role' => 'min_pass', 'is_input' => true, 'contributes_to_total' => false],
            ],
            'SLA' => [
                ['name' => 'Max (SLA)', 'semantic_key' => 'sla_max', 'usage_scope' => 'course_definition', 'total_role' => 'adds_to_total', 'is_input' => true, 'contributes_to_total' => true],
                ['name' => 'Min (SLA)', 'semantic_key' => 'sla_min', 'usage_scope' => 'course_definition', 'total_role' => 'min_pass', 'is_input' => true, 'contributes_to_total' => false],
            ],
        ];

        foreach ($groups as $groupName => $columns) {
            $groupNode = $scheme->assessmentComponents()->updateOrCreate(
                ['parent_id' => $assessmentRoot->id, 'component_code' => Str::slug('assessment-scheme ' . $groupName)],
                $this->assessmentComponentAttributes($groupName, $displayOrder++, $assessmentRoot->id, $supportsMeta)
            );

            foreach ($columns as $column) {
                $scheme->assessmentComponents()->updateOrCreate(
                    ['parent_id' => $groupNode->id, 'component_code' => Str::slug('assessment-scheme ' . $groupName . ' ' . $column['name'])],
                    $this->assessmentComponentAttributes($column['name'], $displayOrder++, $groupNode->id, $supportsMeta, $column)
                );
            }
        }

        $programme->unsetRelation('scheme');
    }

    private function assessmentComponentAttributes(string $name, int $displayOrder, ?int $parentId, bool $supportsMeta, array $column = []): array
    {
        $attributes = [
            'component_name' => $name,
            'display_order' => $displayOrder,
        ];

        if (!$supportsMeta) {
            return $attributes;
        }

        if ($parentId === null || $column === []) {
            return $attributes + [
                'value_kind' => 'group',
                'entry_mode' => 'readonly',
                'total_role' => 'group',
                'is_input' => false,
                'contributes_to_total' => false,
                'usage_scope' => 'display_only',
                'semantic_key' => null,
            ];
        }

        return $attributes + [
            'usage_scope' => $column['usage_scope'] ?? 'course_definition',
            'semantic_key' => $column['semantic_key'] ?? null,
            'value_kind' => 'marks',
            'entry_mode' => ($column['total_role'] ?? null) === 'derived' ? 'readonly' : 'input',
            'total_role' => $column['total_role'] ?? 'adds_to_total',
            'is_input' => $column['is_input'] ?? true,
            'contributes_to_total' => $column['contributes_to_total'] ?? true,
        ];
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

        $course->setAttribute('learning_scheme_rows', $course->programme?->scheme?->getSyllabusLearningHeaderRows() ?? []);
        $course->setAttribute('learning_scheme_leaf_columns', $course->programme?->scheme?->getSyllabusLearningLeafColumns() ?? []);
        $course->setAttribute('learning_scheme_values', $this->buildLearningSchemeValues($course));
        $course->setAttribute('assessment_scheme_rows', $course->programme?->scheme?->getSyllabusAssessmentHeaderRows() ?? []);
        $course->setAttribute('assessment_scheme_leaf_columns', $course->programme?->scheme?->getSyllabusAssessmentLeafColumns() ?? []);
        $course->setAttribute('assessment_scheme_values', $course->assessments->map(function ($assessment) {
            return [
                'component_id' => (int) $assessment->component_id,
                'component_name' => $assessment->component?->component_name,
                'semantic_key' => $assessment->component?->semantic_key,
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
            $semanticKey = strtolower(trim((string) ($a->component?->semantic_key ?? '')));
            $max = is_numeric($a->max_marks) ? (int) $a->max_marks : null;
            if ($max === null) {
                continue;
            }

            if ($semanticKey !== '') {
                if ($semanticKey === 'fa_th_max' && $out['test_max_marks'] === null) {
                    $out['test_max_marks'] = $max;
                    continue;
                }
                if ($semanticKey === 'fa_th_min' && $out['test_min_marks'] === null) {
                    $out['test_min_marks'] = is_numeric($a->min_marks) ? (int) $a->min_marks : $max;
                    continue;
                }
                if ($semanticKey === 'sa_th_max' && $out['theory_max_marks'] === null) {
                    $out['theory_max_marks'] = $max;
                    continue;
                }
                if ($semanticKey === 'sa_th_min' && $out['theory_min_marks'] === null) {
                    $out['theory_min_marks'] = is_numeric($a->min_marks) ? (int) $a->min_marks : $max;
                    continue;
                }
                if ($semanticKey === 'sa_pr_max' && $out['pr_max_marks'] === null) {
                    $out['pr_max_marks'] = $max;
                    continue;
                }
                if ($semanticKey === 'sa_pr_min' && $out['pr_min_marks'] === null) {
                    $out['pr_min_marks'] = is_numeric($a->min_marks) ? (int) $a->min_marks : $max;
                    continue;
                }
                if ($semanticKey === 'oral_max' && $out['or_max_marks'] === null) {
                    $out['or_max_marks'] = $max;
                    continue;
                }
                if (in_array($semanticKey, ['tw_max', 'sla_max'], true) && $out['tw_max_marks'] === null) {
                    $out['tw_max_marks'] = $max;
                    continue;
                }
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

    /**
     * @return array<int, array{component_id:int,component_name:?string,semantic_key:?string,value:int|float|null}>
     */
    private function buildLearningSchemeValues(Course $course): array
    {
        $leafColumns = $course->programme?->scheme?->getSyllabusLearningLeafColumns() ?? [];
        $values = [];

        foreach ($leafColumns as $leaf) {
            $semanticKey = $leaf['semantic_key'] ?? $course->programme?->scheme?->inferLearningSemanticKey((string) ($leaf['name'] ?? ''));
            $value = match ($semanticKey) {
                'th_hours' => (int) $course->th_hours,
                'tu_hours' => (int) $course->tu_hours,
                'pr_hours' => (int) $course->pr_hours,
                'total_hours' => (int) $course->total_hours,
                'credits' => is_numeric($course->credits) ? (float) $course->credits : null,
                'slh_hours' => 0,
                'nlh_hours' => (int) $course->total_hours,
                default => null,
            };

            $values[] = [
                'component_id' => (int) $leaf['id'],
                'component_name' => $leaf['name'] ?? null,
                'semantic_key' => $semanticKey,
                'value' => $value,
            ];
        }

        return $values;
    }
}
