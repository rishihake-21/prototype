<?php

namespace App\Services;

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

        // Metadata explicitly so we don't rely on mass-assignment for system fields
        $syllabus = new Syllabus($data);
        $syllabus->submitted_by = $creator->id;
        $syllabus->status = Syllabus::STATUS_DRAFT;
        $syllabus->version_number = 1;

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
                $assignment->update(['status' => 'in_progress']);
            }
        }

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
}
