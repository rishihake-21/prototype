<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $stats = [
            'total_syllabi' => Syllabus::count(),
            'draft_syllabi' => Syllabus::byStatus(Syllabus::STATUS_DRAFT)->count(),
            'submitted_syllabi' => Syllabus::byStatus(Syllabus::STATUS_SUBMITTED)->count(),
            'approved_syllabi' => Syllabus::byStatus(Syllabus::STATUS_APPROVED)->count(),
            'rejected_syllabi' => Syllabus::byStatus(Syllabus::STATUS_REJECTED)->count(),
            'archived_syllabi' => Syllabus::byStatus(Syllabus::STATUS_ARCHIVED)->count(),
        ];

        $monthlySubmissions = Syllabus::select(
            DB::raw('strftime("%Y-%m", created_at) as month'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $departmentStats = Syllabus::select('departments.name', DB::raw('count(*) as count'))
            ->join('course_department', 'syllabi.id', '=', 'course_department.syllabus_id')
            ->join('departments', 'course_department.department_id', '=', 'departments.id')
            ->groupBy('departments.name')
            ->get();

        $topCreators = User::where('role', User::ROLE_FACULTY)
            ->withCount('syllabiCreated')
            ->orderByDesc('syllabi_created_count')
            ->take(10)
            ->get();

        $averageApprovalTime = Syllabus::whereNotNull('approved_at')
            ->whereNotNull('submitted_at')
            ->select(DB::raw('AVG(julianday(approved_at) - julianday(submitted_at)) as avg_days'))
            ->first();

        return view('admin.analytics.index', compact(
            'stats',
            'monthlySubmissions',
            'departmentStats',
            'topCreators',
            'averageApprovalTime'
        ));
    }
}
