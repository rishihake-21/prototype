<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Syllabus;
use App\Models\User;

class SyllabusService
{
    public function __construct(
        private VersioningService $versioningService,
        private AuditService $auditService
    ) {}

    public function create(array $data, User $creator): Syllabus
    {
        $departmentIds = $data['department_ids'] ?? [];
        unset($data['department_ids']);

        // Ensure legacy/non-form fields required by the database are populated.
        // The current multi-step form does not send some of the original fields,
        // but older schemas keep them as NOT NULL. Provide safe defaults.
        if (!array_key_exists('course_description', $data) || $data['course_description'] === null) {
            $data['course_description'] = $data['rationale'] ?? '';
        }

        if (!array_key_exists('learning_outcomes', $data) || $data['learning_outcomes'] === null) {
            // Stored as JSON; empty array is a safe default.
            $data['learning_outcomes'] = [];
        }

        // Credits and other legacy core fields
        $teachingScheme = $data['teaching_scheme'] ?? [];
        if (!array_key_exists('credits', $data) || $data['credits'] === null) {
            $data['credits'] = $teachingScheme['credits'] ?? 0;
        }

        // Map SLH and NLH to top-level columns
        if (!array_key_exists('slh_hours', $data) || $data['slh_hours'] === null) {
            $data['slh_hours'] = $teachingScheme['slh_hours'] ?? 0;
        }
        if (!array_key_exists('nlh_hours', $data) || $data['nlh_hours'] === null) {
            $data['nlh_hours'] = $teachingScheme['nlh_hours'] ?? 0;
        }

        $examScheme = $data['examination_scheme'] ?? [];
        if (!array_key_exists('tw_marks', $data) || $data['tw_marks'] === null) {
            $data['tw_marks'] = $examScheme['tw_marks'] ?? 0;
        }

        if (!array_key_exists('duration_weeks', $data) || $data['duration_weeks'] === null) {
            $data['duration_weeks'] = 0;
        }

        if (!array_key_exists('instructor_name', $data) || $data['instructor_name'] === null) {
            $data['instructor_name'] = $creator->name ?? '';
        }

        if (!array_key_exists('instructor_email', $data) || $data['instructor_email'] === null) {
            $data['instructor_email'] = $creator->email ?? '';
        }

        if (!array_key_exists('level', $data) || $data['level'] === null) {
            $data['level'] = Syllabus::LEVEL_UNDERGRADUATE;
        }

        if (!array_key_exists('semester', $data) || $data['semester'] === null) {
            $data['semester'] = Syllabus::SEMESTER_FALL;
        }

        if (!array_key_exists('year', $data) || $data['year'] === null) {
            // Derive from academic_year if present, otherwise current year.
            if (!empty($data['academic_year']) && is_string($data['academic_year'])) {
                $parts = explode('-', $data['academic_year']);
                $data['year'] = (int) ($parts[0] ?? date('Y'));
            } else {
                $data['year'] = (int) date('Y');
            }
        }

        if (!array_key_exists('objectives', $data) || $data['objectives'] === null) {
            $data['objectives'] = $data['rationale'] ?? '';
        }

        if (!array_key_exists('topics', $data) || $data['topics'] === null) {
            $data['topics'] = [];
        }

        if (!array_key_exists('assessments', $data) || $data['assessments'] === null) {
            $data['assessments'] = [];
        }

        if (!array_key_exists('grading_policy', $data) || $data['grading_policy'] === null) {
            $data['grading_policy'] = '';
        }

        if (!array_key_exists('policies', $data) || $data['policies'] === null) {
            $data['policies'] = '';
        }

        if (!array_key_exists('industry_employer_outcome', $data) || $data['industry_employer_outcome'] === null) {
            $data['industry_employer_outcome'] = '';
        }

        if (!array_key_exists('self_learning', $data) || $data['self_learning'] === null) {
            $data['self_learning'] = 'Not Applicable';
        }

        if (!array_key_exists('special_instructional_strategies', $data) || $data['special_instructional_strategies'] === null) {
            $data['special_instructional_strategies'] = [];
        }

        // If starting from an assignment, sync metadata and prevent duplicates
        if (!empty($data['assignment_id'])) {
            $assignment = \App\Models\CourseAssignment::with('syllabi')->find($data['assignment_id']);
            if ($assignment) {
                $existing = $assignment->syllabi()
                    ->where('submitted_by', $creator->id)
                    ->whereIn('status', [Syllabus::STATUS_DRAFT, Syllabus::STATUS_REJECTED, Syllabus::STATUS_CHANGES_REQUESTED])
                    ->first();
                
                if ($existing) {
                    return $existing; // Silently return existing instead of duplicate
                }

                $data['course_id'] = $assignment->course_id;
                $assignment->update(['status' => CourseAssignment::STATUS_IN_PROGRESS]);
            }
        }

        [$data, $departmentIds] = $this->syncLockedCourseDefinition($data, $departmentIds);

        // Metadata explicitly so we don't rely on mass-assignment for system fields
        $syllabus = new Syllabus($data);
        $syllabus->submitted_by = $creator->id;
        $syllabus->status = Syllabus::STATUS_DRAFT;
        $syllabus->version_number = 1;

        $syllabus->save();

        if (!empty($departmentIds)) {
            $syllabus->departments()->attach($departmentIds);
        }

        $this->auditService->log('syllabus.created', $syllabus, [], $syllabus->toArray());

        return $syllabus;
    }

    public function update(Syllabus $syllabus, array $data, User $user): Syllabus
    {
        $oldValues = $syllabus->toArray();

        $departmentIds = $data['department_ids'] ?? null;
        if ($departmentIds !== null) {
            unset($data['department_ids']);
        }

        // Keep legacy fields in sync / non-null for existing records too.
        if (!array_key_exists('course_description', $data) || $data['course_description'] === null) {
            $data['course_description'] = $data['rationale'] ?? $syllabus->course_description ?? '';
        }

        if (!array_key_exists('learning_outcomes', $data) || $data['learning_outcomes'] === null) {
            $data['learning_outcomes'] = $syllabus->learning_outcomes ?? [];
        }

        if (!array_key_exists('credits', $data) || $data['credits'] === null) {
            $data['credits'] = $syllabus->credits ?? 0;
        }

        if (!array_key_exists('duration_weeks', $data) || $data['duration_weeks'] === null) {
            $data['duration_weeks'] = $syllabus->duration_weeks ?? 0;
        }

        if (!array_key_exists('instructor_name', $data) || $data['instructor_name'] === null) {
            $data['instructor_name'] = $syllabus->instructor_name ?? '';
        }

        if (!array_key_exists('instructor_email', $data) || $data['instructor_email'] === null) {
            $data['instructor_email'] = $syllabus->instructor_email ?? '';
        }

        if (!array_key_exists('level', $data) || $data['level'] === null) {
            $data['level'] = $syllabus->level ?? Syllabus::LEVEL_UNDERGRADUATE;
        }

        if (!array_key_exists('semester', $data) || $data['semester'] === null) {
            $data['semester'] = $syllabus->semester ?? Syllabus::SEMESTER_FALL;
        }

        if (!array_key_exists('year', $data) || $data['year'] === null) {
            $data['year'] = $syllabus->year ?? (int) date('Y');
        }

        if (!array_key_exists('objectives', $data) || $data['objectives'] === null) {
            $data['objectives'] = $syllabus->objectives ?? ($data['rationale'] ?? '');
        }

        if (!array_key_exists('topics', $data) || $data['topics'] === null) {
            $data['topics'] = $syllabus->topics ?? [];
        }

        if (!array_key_exists('assessments', $data) || $data['assessments'] === null) {
            $data['assessments'] = $syllabus->assessments ?? [];
        }

        if (!array_key_exists('grading_policy', $data) || $data['grading_policy'] === null) {
            $data['grading_policy'] = $syllabus->grading_policy ?? '';
        }

        if (!array_key_exists('policies', $data) || $data['policies'] === null) {
            $data['policies'] = $syllabus->policies ?? '';
        }

        if (!array_key_exists('industry_employer_outcome', $data) || $data['industry_employer_outcome'] === null) {
            $data['industry_employer_outcome'] = $syllabus->industry_employer_outcome ?? '';
        }

        if (!array_key_exists('self_learning', $data) || $data['self_learning'] === null) {
            $data['self_learning'] = $syllabus->self_learning ?? 'Not Applicable';
        }

        if (!array_key_exists('special_instructional_strategies', $data) || $data['special_instructional_strategies'] === null) {
            $data['special_instructional_strategies'] = $syllabus->special_instructional_strategies ?? [];
        }

        [$data, $departmentIds] = $this->syncLockedCourseDefinition($data, $departmentIds);

        $syllabus->fill($data);
        $syllabus->save();

        if ($departmentIds !== null) {
            $syllabus->departments()->sync($departmentIds);
        }

        $this->auditService->log('syllabus.updated', $syllabus, $oldValues, $syllabus->toArray());

        return $syllabus;
    }

    public function clone(Syllabus $source, User $creator): Syllabus
    {
        $data = $source->toArray();
        
        unset($data['id'], $data['submitted_by'], $data['approved_by'], $data['submitted_at'], 
              $data['approved_at'], $data['created_at'], $data['updated_at'], $data['deleted_at'],
              $data['file_path'], $data['rejection_reason']);
        
        $data['status'] = Syllabus::STATUS_DRAFT;
        $data['version_number'] = 1;
        
        $clone = new Syllabus($data);
        $clone->submitted_by = $creator->id;
        $clone->save();

        // Preserve department associations from the source syllabus
        $departmentIds = $source->departments->pluck('id')->toArray();
        if (!empty($departmentIds)) {
            $clone->departments()->sync($departmentIds);
        }
        
        $this->auditService->log('syllabus.cloned', $clone, ['source_id' => $source->id], $clone->toArray());
        
        return $clone;
    }

    public function canEdit(Syllabus $syllabus, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isFaculty()) {
            // If they are the creator, they should be able to edit it if it's in an editable state
            return $syllabus->submitted_by === $user->id &&
                   $syllabus->canBeEdited();
        }

        return false;
    }

    public function canView(Syllabus $syllabus, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isFaculty() && $syllabus->submitted_by === $user->id) {
            return true;
        }

        if ($user->isHod()) {
            $syllabusDepartmentIds = $syllabus->departments->pluck('id')->toArray();
            
            // Collect all department IDs the user is associated with (as member OR as head)
            $userDepartmentIds = $user->departments->pluck('id')->toArray();
            if ($user->headedDepartment) {
                $userDepartmentIds[] = $user->headedDepartment->id;
            }
            $userDepartmentIds = array_unique($userDepartmentIds);

            return count(array_intersect($syllabusDepartmentIds, $userDepartmentIds)) > 0 &&
                   in_array($syllabus->status, [
                       Syllabus::STATUS_SUBMITTED,
                       Syllabus::STATUS_UNDER_REVIEW,
                       Syllabus::STATUS_APPROVED,
                       Syllabus::STATUS_REJECTED,
                       Syllabus::STATUS_CHANGES_REQUESTED
                   ]);
        }

        if ($user->isObserver() && $syllabus->isApproved()) {
            return true;
        }

        return false;
    }

    /**
     * Keep syllabus identity and scheme fields aligned with the linked CDC course definition.
     *
     * @param array<string, mixed> $data
     * @param array<int, mixed>|null $departmentIds
     * @return array{0: array<string, mixed>, 1: array<int, mixed>|null}
     */
    private function syncLockedCourseDefinition(array $data, ?array $departmentIds): array
    {
        $courseId = $data['course_id'] ?? null;
        if (!$courseId) {
            return [$data, $departmentIds];
        }

        $course = Course::with(['programme', 'level', 'departments', 'assessments.component'])->find($courseId);
        if (!$course) {
            return [$data, $departmentIds];
        }

        $assessment = $this->mapCourseAssessmentToSyllabusScheme($course);
        $existingTeaching = is_array($data['teaching_scheme'] ?? null) ? $data['teaching_scheme'] : [];
        $existingExam = is_array($data['examination_scheme'] ?? null) ? $data['examination_scheme'] : [];

        $data['course_id'] = $course->id;
        $data['title'] = $course->course_title;
        $data['course_code'] = $course->course_code;
        $data['program_name'] = $course->programme?->code ?? ($data['program_name'] ?? '');
        $data['academic_year'] = $course->programme?->academic_year ?? ($data['academic_year'] ?? '');
        $data['scheme_type'] = $course->programme?->scheme_type ?? ($data['scheme_type'] ?? 'standard');
        $data['level'] = $course->level?->sort_order ?? ($data['level'] ?? null);
        $data['elective_group'] = $course->course_type === Course::TYPE_ELECTIVE
            ? ($course->elective_group ?? null)
            : null;

        $data['teaching_scheme'] = array_merge($existingTeaching, [
            'th_hours' => (int) $course->th_hours,
            'tu_hours' => (int) $course->tu_hours,
            'pr_hours' => (int) $course->pr_hours,
            'credits' => (float) $course->credits,
            'total_hours' => (int) $course->total_hours,
            'slh_hours' => (int) ($existingTeaching['slh_hours'] ?? 0),
            'nlh_hours' => (int) $course->total_hours + (int) ($existingTeaching['slh_hours'] ?? 0),
        ]);

        $data['examination_scheme'] = array_merge($existingExam, [
            'fa_th_max' => $assessment['fa_th_max'],
            'fa_th_min' => $assessment['fa_th_min'],
            'sa_th_max' => $assessment['sa_th_max'],
            'sa_th_min' => $assessment['sa_th_min'],
            'sa_pr_max' => $assessment['sa_pr_max'],
            'sa_pr_min' => $assessment['sa_pr_min'],
            'tw_marks' => $assessment['tw_marks'],
            'paper_duration' => $course->theory_paper_hrs,
        ]);

        $ownedDepartmentId = $course->programme?->department_id;
        $mappedDepartmentIds = $course->departments->pluck('id')->map(fn ($id) => (int) $id)->all();
        $resolvedDepartmentIds = collect($mappedDepartmentIds)
            ->merge($ownedDepartmentId ? [(int) $ownedDepartmentId] : [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($resolvedDepartmentIds)) {
            $departmentIds = $resolvedDepartmentIds;
        }

        return [$data, $departmentIds];
    }

    /**
     * @return array<string, int|null>
     */
    private function mapCourseAssessmentToSyllabusScheme(Course $course): array
    {
        $out = [
            'fa_th_max' => null,
            'fa_th_min' => null,
            'sa_th_max' => null,
            'sa_th_min' => null,
            'sa_pr_max' => null,
            'sa_pr_min' => null,
            'tw_marks' => null,
        ];

        foreach ($course->assessments as $assessment) {
            $name = strtolower(trim((string) ($assessment->component?->component_name ?? '')));
            $max = is_numeric($assessment->max_marks) ? (int) $assessment->max_marks : null;
            $min = is_numeric($assessment->min_marks) ? (int) $assessment->min_marks : null;

            if (($out['fa_th_max'] === null) && str_contains($name, 'fa-th') && str_contains($name, 'max')) {
                $out['fa_th_max'] = $max;
                continue;
            }
            if (($out['fa_th_min'] === null) && str_contains($name, 'fa-th') && str_contains($name, 'min')) {
                $out['fa_th_min'] = $min ?? $max;
                continue;
            }
            if (($out['sa_th_max'] === null) && str_contains($name, 'sa-th') && str_contains($name, 'max')) {
                $out['sa_th_max'] = $max;
                continue;
            }
            if (($out['sa_th_min'] === null) && str_contains($name, 'sa-th') && str_contains($name, 'min')) {
                $out['sa_th_min'] = $min ?? $max;
                continue;
            }
            if (($out['sa_pr_max'] === null) && str_contains($name, 'sa-pr') && str_contains($name, 'max')) {
                $out['sa_pr_max'] = $max;
                continue;
            }
            if (($out['sa_pr_min'] === null) && str_contains($name, 'sa-pr') && str_contains($name, 'min')) {
                $out['sa_pr_min'] = $min ?? $max;
                continue;
            }
            if (($out['tw_marks'] === null) && (str_contains($name, 'tw') || str_contains($name, 'sla')) && str_contains($name, 'max')) {
                $out['tw_marks'] = $max;
            }
        }

        $out['fa_th_max'] ??= 0;
        $out['fa_th_min'] ??= 0;
        $out['sa_th_max'] ??= 0;
        $out['sa_th_min'] ??= 0;
        $out['sa_pr_max'] ??= 0;
        $out['sa_pr_min'] ??= 0;
        $out['tw_marks'] ??= 0;

        return $out;
    }
}
