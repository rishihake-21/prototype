<?php

namespace App\Http\Controllers;

use App\Models\Syllabus;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * @return array<int, string>
     */
    private function handoffTypes(): array
    {
        return [
            \App\Models\Notification::TYPE_ELECTIVE_POOL_UPDATED,
            \App\Models\Notification::TYPE_ELECTIVE_SELECTED,
        ];
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('dashboard.admin');
        } elseif ($user->isHod()) {
            return redirect()->route('dashboard.approver');
        } elseif ($user->isCdc()) {
            return redirect()->route('dashboard.cdc');
        } elseif ($user->isObserver()) {
            return redirect()->route('dashboard.observer');
        } else {
            return redirect()->route('dashboard.creator');
        }
    }

    public function observer()
    {
        $user = auth()->user();

        $stats = [
            'total_approved' => Syllabus::byStatus(Syllabus::STATUS_APPROVED)->count(),
            'total_programmes' => \App\Models\Programme::count(),
        ];

        $approvedSyllabi = Syllabus::byStatus(Syllabus::STATUS_APPROVED)
            ->with(['creator', 'departments'])
            ->latest('approved_at')
            ->paginate(12);

        return view('dashboard.observer', compact('stats', 'approvedSyllabi'));
    }

    public function cdc()
    {
        $user = auth()->user();

        $stats = [
            'total_programmes' => \App\Models\Programme::count(),
            'active_programmes' => \App\Models\Programme::where('status', 'active')->count(),
            'total_courses' => \App\Models\Course::count(),
            'unread_handoffs' => $user->notifications()->whereIn('type', $this->handoffTypes())->unread()->count(),
        ];

        $recentProgrammes = \App\Models\Programme::with('creator')
            ->latest()
            ->take(5)
            ->get();

        $handoffs = $user->notifications()
            ->whereIn('type', $this->handoffTypes())
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.cdc', compact('stats', 'recentProgrammes', 'handoffs'));
    }

    public function creator()
    {
        $user = auth()->user();

        $stats = [
            'total' => $user->syllabiCreated()->count(),
            'drafts' => $user->syllabiCreated()->byStatus(Syllabus::STATUS_DRAFT)->count(),
            'pending' => $user->syllabiCreated()->byStatus(Syllabus::STATUS_SUBMITTED)->count(),
            'approved' => $user->syllabiCreated()->byStatus(Syllabus::STATUS_APPROVED)->count(),
            'rejected' => $user->syllabiCreated()->byStatus(Syllabus::STATUS_REJECTED)->count(),
        ];

        $assignments = \App\Models\CourseAssignment::where('faculty_user_id', $user->id)
            ->where('status', 'pending')
            ->with(['course.programme', 'course.level', 'assigner'])
            ->get();

        $recentSyllabi = $user->syllabiCreated()
            ->with('departments')
            ->latest()
            ->take(5)
            ->get();

        $rejectedSyllabi = $user->syllabiCreated()
            ->byStatus(Syllabus::STATUS_REJECTED)
            ->with('departments')
            ->latest()
            ->take(3)
            ->get();

        return view('dashboard.creator', compact('stats', 'recentSyllabi', 'rejectedSyllabi', 'assignments'));
    }

    public function approver()
    {
        $user = auth()->user();

        // Use the centralized approver scope which already
        // filters by status and associated departments.
        $pendingQuery = Syllabus::query()->forApprover($user);

        $stats = [
            'pending' => $pendingQuery->count(),
            'approved_this_month' => $user->reviews()
                ->where('status', 'approved')
                ->whereMonth('review_date', now()->month)
                ->count(),
            'rejected_this_month' => $user->reviews()
                ->where('status', 'rejected')
                ->whereMonth('review_date', now()->month)
                ->count(),
            'unread_handoffs' => $user->notifications()->whereIn('type', $this->handoffTypes())->unread()->count(),
        ];

        $reviewQueue = Syllabus::query()
            ->forApprover($user)
            ->whereIn('status', [Syllabus::STATUS_SUBMITTED, Syllabus::STATUS_UNDER_REVIEW, Syllabus::STATUS_CHANGES_REQUESTED])
            ->with(['creator', 'departments'])
            ->oldest('submitted_at')
            ->paginate(10);

        $handoffs = $user->notifications()
            ->whereIn('type', $this->handoffTypes())
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.approver', compact('stats', 'reviewQueue', 'handoffs'));
    }
}
