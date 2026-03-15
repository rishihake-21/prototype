<?php

namespace App\Services;

use App\Models\CourseAssignment;
use App\Models\Syllabus;
use App\Models\SyllabusReview;
use App\Models\User;
use App\Models\Notification;
use App\Notifications\SyllabusApprovedNotification;
use App\Notifications\SyllabusRejectedNotification;
use App\Notifications\SyllabusSubmittedNotification;
use Carbon\Carbon;

class WorkflowService
{
    public function __construct(
        private VersioningService $versioningService,
        private AuditService $auditService
    ) {}

    public function submit(Syllabus $syllabus, User $submitter): void
    {
        if (!$syllabus->canBeSubmitted()) {
            throw new \InvalidArgumentException('Syllabus cannot be submitted in current state');
        }

        $oldStatus = $syllabus->status;
        
        $this->versioningService->createSnapshot($syllabus);
        
        $syllabus->status = Syllabus::STATUS_SUBMITTED;
        $syllabus->submitted_at = Carbon::now();
        
        if (in_array($oldStatus, [Syllabus::STATUS_REJECTED, Syllabus::STATUS_CHANGES_REQUESTED], true)) {
            $syllabus->version_number++;
            $syllabus->rejection_reason = null;
        }
        
        $syllabus->save();

        $this->syncAssignmentStatus($syllabus, CourseAssignment::STATUS_SUBMITTED);
        
        $this->auditService->log('syllabus.submitted', $syllabus, ['status' => $oldStatus], ['status' => $syllabus->status]);
        
        // Notify HODs from all associated departments (SRS: HOD reviews own department)
        $departmentIds = $syllabus->departments->pluck('id')->toArray();
        $approvers = User::where('role', 'hod')
            ->whereHas('departments', function ($q) use ($departmentIds) {
                $q->whereIn('departments.id', $departmentIds);
            })
            ->get();
        
        foreach ($approvers as $approver) {
            Notification::create([
                'user_id' => $approver->id,
                'type' => Notification::TYPE_SYLLABUS_SUBMITTED,
                'title' => 'New Syllabus Submitted for Review',
                'message' => "A new syllabus '{$syllabus->title}' has been submitted for your department.",
                'data' => ['syllabus_id' => $syllabus->id]
            ]);
        }
    }

    public function startReview(Syllabus $syllabus, User $reviewer): void
    {
        if (!$syllabus->isSubmitted()) {
            throw new \InvalidArgumentException('Syllabus must be submitted to start review');
        }

        $oldStatus = $syllabus->status;
        $syllabus->status = Syllabus::STATUS_UNDER_REVIEW;
        $syllabus->save();

        $this->syncAssignmentStatus($syllabus, CourseAssignment::STATUS_UNDER_REVIEW);
        
        $this->auditService->log('syllabus.under_review', $syllabus, ['status' => $oldStatus], ['status' => $syllabus->status]);
    }

    public function approve(Syllabus $syllabus, User $approver, ?string $comments = null): void
    {
        if (!$syllabus->canBeApprovedOrRejected()) {
            throw new \InvalidArgumentException('Syllabus cannot be approved in current state');
        }

        $oldStatus = $syllabus->status;
        
        $syllabus->status = Syllabus::STATUS_APPROVED;
        $syllabus->approved_by = $approver->id;
        $syllabus->approved_at = Carbon::now();
        $syllabus->save();
        
        SyllabusReview::create([
            'syllabus_id' => $syllabus->id,
            'reviewer_id' => $approver->id,
            'status' => 'approved',
            'comments' => $comments,
            'version_reviewed' => $syllabus->version_number,
            'review_date' => Carbon::now(),
        ]);
        
        $this->auditService->log('syllabus.approved', $syllabus, ['status' => $oldStatus], ['status' => $syllabus->status]);
        
        // Update linked assignment status if exists
        $this->syncAssignmentStatus($syllabus, CourseAssignment::STATUS_COMPLETED);

        // Create in-app notification
        Notification::create([
            'user_id' => $syllabus->creator->id,
            'type' => Notification::TYPE_SYLLABUS_APPROVED,
            'title' => 'Syllabus Approved',
            'message' => "Your syllabus '{$syllabus->title}' has been approved.",
            'data' => ['syllabus_id' => $syllabus->id]
        ]);
        
        dispatch(new \App\Jobs\GeneratePdfJob($syllabus));
    }

    public function reject(Syllabus $syllabus, User $reviewer, string $reason): void
    {
        if (!$syllabus->canBeApprovedOrRejected()) {
            throw new \InvalidArgumentException('Syllabus cannot be rejected in current state');
        }

        if (strlen($reason) < 20) {
            throw new \InvalidArgumentException('Rejection reason must be at least 20 characters');
        }

        $oldStatus = $syllabus->status;
        
        $syllabus->status = Syllabus::STATUS_REJECTED;
        $syllabus->rejection_reason = $reason;
        $syllabus->save();
        
        SyllabusReview::create([
            'syllabus_id' => $syllabus->id,
            'reviewer_id' => $reviewer->id,
            'status' => 'rejected',
            'comments' => $reason,
            'version_reviewed' => $syllabus->version_number,
            'review_date' => Carbon::now(),
        ]);
        
        $this->auditService->log('syllabus.rejected', $syllabus, ['status' => $oldStatus], ['status' => $syllabus->status]);

        $this->syncAssignmentStatus($syllabus, CourseAssignment::STATUS_REJECTED);
        
        // Create in-app notification
        Notification::create([
            'user_id' => $syllabus->creator->id,
            'type' => Notification::TYPE_SYLLABUS_REJECTED,
            'title' => 'Syllabus Rejected',
            'message' => "Your syllabus '{$syllabus->title}' has been rejected. Reason: {$reason}",
            'data' => ['syllabus_id' => $syllabus->id]
        ]);
    }

    public function requestChanges(Syllabus $syllabus, User $reviewer, string $comments): void
    {
        if (!$syllabus->canRequestChanges()) {
            throw new \InvalidArgumentException('Syllabus cannot have changes requested in current state');
        }

        if (strlen($comments) < 10) {
            throw new \InvalidArgumentException('Comments must be at least 10 characters');
        }

        $oldStatus = $syllabus->status;

        $syllabus->status = Syllabus::STATUS_CHANGES_REQUESTED;
        $syllabus->rejection_reason = $comments;
        $syllabus->save();

        SyllabusReview::create([
            'syllabus_id' => $syllabus->id,
            'reviewer_id' => $reviewer->id,
            'status' => 'changes_requested',
            'comments' => $comments,
            'version_reviewed' => $syllabus->version_number,
            'review_date' => Carbon::now(),
        ]);

        $this->auditService->log('syllabus.changes_requested', $syllabus, ['status' => $oldStatus], ['status' => $syllabus->status]);

        $this->syncAssignmentStatus($syllabus, CourseAssignment::STATUS_CHANGES_REQUESTED);

        // Create in-app notification
        Notification::create([
            'user_id' => $syllabus->creator->id,
            'type' => Notification::TYPE_CHANGES_REQUESTED,
            'title' => 'Changes Requested for Syllabus',
            'message' => "Your syllabus '{$syllabus->title}' requires changes. Please review the feedback and resubmit.",
            'data' => ['syllabus_id' => $syllabus->id]
        ]);
    }

    public function archive(Syllabus $syllabus, User $admin): void
    {
        if (!$syllabus->isApproved()) {
            throw new \InvalidArgumentException('Only approved syllabi can be archived');
        }

        $oldStatus = $syllabus->status;
        $syllabus->status = Syllabus::STATUS_ARCHIVED;
        $syllabus->save();
        
        $this->auditService->log('syllabus.archived', $syllabus, ['status' => $oldStatus], ['status' => $syllabus->status]);
    }

    private function syncAssignmentStatus(Syllabus $syllabus, string $status): void
    {
        if (!$syllabus->assignment_id) {
            return;
        }

        CourseAssignment::where('id', $syllabus->assignment_id)->update(['status' => $status]);
    }
}
