<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Cdc\CourseController;
use App\Http\Controllers\Cdc\GlobalLevelController;
use App\Http\Controllers\Cdc\ProgrammeController;
use App\Http\Controllers\Cdc\ProgrammeStructureController;
use App\Http\Controllers\Cdc\SamplePathController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SyllabusController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Debug route for testing
Route::get('/debug-form', function () {
    return view('syllabi.create');
})->name('debug.form');

require __DIR__ . '/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/creator', [DashboardController::class, 'creator'])->name('dashboard.creator');
    Route::get('/dashboard/approver', [DashboardController::class, 'approver'])->name('dashboard.approver');
    Route::get('/dashboard/cdc', [DashboardController::class, 'cdc'])->name('dashboard.cdc');
    Route::get('/dashboard/observer', [DashboardController::class, 'observer'])->name('dashboard.observer');
    Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])->name('dashboard.admin');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::resource('syllabi', SyllabusController::class);
    Route::post('syllabi/{syllabus}/submit', [SyllabusController::class, 'submit'])->name('syllabi.submit');
    Route::patch('syllabi/{syllabus}/approve', [SyllabusController::class, 'approve'])->name('syllabi.approve');
    Route::patch('syllabi/{syllabus}/reject', [SyllabusController::class, 'reject'])->name('syllabi.reject');
    Route::post('syllabi/{syllabus}/clone', [SyllabusController::class, 'clone'])->name('syllabi.clone');
    Route::get('syllabi/{syllabus}/download-pdf', [SyllabusController::class, 'downloadPdf'])->name('syllabi.download-pdf');
    Route::get('syllabi/{syllabus}/download-docx', [SyllabusController::class, 'downloadDocx'])->name('syllabi.download-docx');
    Route::get('syllabi/{syllabus}/history', [SyllabusController::class, 'history'])->name('syllabi.history');

    // Notifications
    Route::post('notifications/{notification}/read', function (\App\Models\Notification $notification) {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        $notification->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.read');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::resource('departments', DepartmentController::class);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    // ── CDC: Programme Structure & Course Management ───────────────────────
    Route::prefix('cdc')->name('cdc.')->middleware('role:cdc')->group(function () {
        // Scheme Definitions
        Route::resource('schemes', \App\Http\Controllers\Cdc\SchemeController::class)->except(['show']);

        // Programmes (CRUD)
        Route::resource('programmes', ProgrammeController::class);

        // Scheme at a Glance (structure per programme)
        Route::get('programmes/{programme}/structure',
            [ProgrammeStructureController::class, 'index'])->name('programmes.structure');
        Route::put('programmes/{programme}/structure',
            [ProgrammeStructureController::class, 'update'])->name('programmes.structure.update');
        Route::post('programmes/{programme}/structure/calculate',
            [ProgrammeStructureController::class, 'calculate'])->name('programmes.structure.calculate');

        // Level-wise Course Definition (Bulk & Individual)
        Route::get('programmes/{programme}/courses',
            [CourseController::class, 'index'])->name('courses.index');
        Route::get('programmes/{programme}/courses/create',
            [CourseController::class, 'create'])->name('courses.create');
        Route::post('programmes/{programme}/courses',
            [CourseController::class, 'store'])->name('courses.store');
        Route::get('programmes/{programme}/courses/{course}/edit',
            [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('programmes/{programme}/courses/{course}',
            [CourseController::class, 'update'])->name('courses.update');
        Route::delete('programmes/{programme}/courses/{course}',
            [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('courses/{course}/clone',
            [CourseController::class, 'clone'])->name('courses.clone');
        Route::post('programmes/{programme}/courses/{course}/assign-elective',
            [CourseController::class, 'assignElective'])->name('courses.assign-elective');

        // Sample Path (term-wise distribution)
        Route::get('programmes/{programme}/sample-path',
            [SamplePathController::class, 'index'])->name('programmes.sample-path');
        Route::put('programmes/{programme}/sample-path',
            [SamplePathController::class, 'update'])->name('programmes.sample-path.update');

        // Award of Class courses management
        Route::get('programmes/{programme}/award-class',
            [\App\Http\Controllers\Cdc\AwardClassController::class, 'index'])->name('programmes.award-class');
        Route::put('programmes/{programme}/award-class',
            [\App\Http\Controllers\Cdc\AwardClassController::class, 'update'])->name('programmes.award-class.update');
    });

    // ── HOD: Subject Assignments ──────────────────────────────────────────
    Route::prefix('hod')->name('hod.')->middleware('role:hod')->group(function () {
        Route::get('assignments', [\App\Http\Controllers\Hod\AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('assignments', [\App\Http\Controllers\Hod\AssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('assignments/{assignment}', [\App\Http\Controllers\Hod\AssignmentController::class, 'destroy'])->name('assignments.destroy');
    });

    // ── API: Helpers ──────────────────────────────────────────
    Route::get('api/courses/{code}', [CourseController::class, 'apiShow'])->name('api.courses.show');
    Route::get('api/schemes/{scheme}/leaf-components', [\App\Http\Controllers\Cdc\SchemeController::class, 'apiLeafComponents'])->name('api.schemes.leaf-components');
});
