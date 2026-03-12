<?php

namespace App\Policies;

use App\Models\Syllabus;
use App\Models\User;

class SyllabusPolicy
{
    public function view(User $user, Syllabus $syllabus): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCreator() && $syllabus->submitted_by === $user->id) {
            return true;
        }

        if ($user->isApprover()) {
            $syllabusDepartmentIds = $syllabus->departments->pluck('id')->toArray();
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

    public function create(User $user): bool
    {
        return $user->isCreator() || $user->isAdmin();
    }

    public function update(User $user, Syllabus $syllabus): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isCreator() 
            && $syllabus->submitted_by === $user->id 
            && $syllabus->canBeEdited();
    }

    public function delete(User $user, Syllabus $syllabus): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isCreator() 
            && $syllabus->submitted_by === $user->id 
            && $syllabus->isDraft();
    }

    public function submit(User $user, Syllabus $syllabus): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isCreator() 
            && $syllabus->submitted_by === $user->id 
            && $syllabus->canBeEdited();
    }

    public function approve(User $user, Syllabus $syllabus): bool
    {
        return ($user->isApprover() || $user->isAdmin())
            && in_array($syllabus->status, [
                Syllabus::STATUS_SUBMITTED,
                Syllabus::STATUS_UNDER_REVIEW,
                Syllabus::STATUS_CHANGES_REQUESTED,
            ]);
    }

    public function reject(User $user, Syllabus $syllabus): bool
    {
        return ($user->isApprover() || $user->isAdmin())
            && in_array($syllabus->status, [
                Syllabus::STATUS_SUBMITTED,
                Syllabus::STATUS_UNDER_REVIEW,
                Syllabus::STATUS_CHANGES_REQUESTED,
            ]);
    }

    public function download(User $user, Syllabus $syllabus): bool
    {
        if (!$syllabus->isApproved()) {
            return false;
        }

        return $this->view($user, $syllabus);
    }
}
