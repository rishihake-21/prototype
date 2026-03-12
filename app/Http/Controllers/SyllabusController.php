<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSyllabusRequest;
use App\Http\Requests\UpdateSyllabusRequest;
use App\Models\Programme;
use App\Models\Syllabus;
use App\Services\DocxService;
use App\Services\PdfService;
use App\Services\SyllabusService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SyllabusController extends Controller
{
    public function __construct(
        private SyllabusService $syllabusService,
        private WorkflowService $workflowService,
        private PdfService $pdfService,
        private DocxService $docxService
    ) {}

    public function index(Request $request)
    {
        $query = Syllabus::query();

        if (auth()->user()->isCreator()) {
            $query->forCreator(auth()->user());
        } elseif (auth()->user()->isApprover()) {
            $query->forApprover(auth()->user());
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%")
                  ->orWhere('instructor_name', 'like', "%{$search}%");
            });
        }

        $syllabi = $query->with(['creator', 'departments', 'approver'])
            ->latest()
            ->paginate(15);

        return view('syllabi.index', compact('syllabi'));
    }

    public function create(Request $request)
    {
        $assignmentId = $request->query('assignment');
        $assignment = null;
        
        if ($assignmentId) {
            $assignment = \App\Models\CourseAssignment::with(['course.programme', 'course.level', 'syllabi'])->find($assignmentId);
            
            // Authorization: assignment must belong to current user
            if ($assignment && $assignment->faculty_user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to assignment.');
            }

            // If a draft/rejected copy already exists for this assignment, redirect to edit instead of showing create form
            if ($assignment) {
                $existing = $assignment->syllabi()->where('submitted_by', auth()->id())->first();
                if ($existing && $existing->canBeEdited()) {
                    return redirect()->route('syllabi.edit', $existing)
                        ->with('info', 'You already have a draft for this assignment. Pick up where you left off.');
                }
            }
        }

        $programmesMetadata = Programme::where('status', Programme::STATUS_ACTIVE)
            ->get(['code', 'scheme_type'])
            ->mapWithKeys(fn ($programme) => [
                $programme->code => Programme::normalizeSchemeType($programme->scheme_type),
            ])
            ->toArray();

        return view('syllabi.create', compact('assignment', 'programmesMetadata'));
    }

    public function store(StoreSyllabusRequest $request)
    {
        $syllabus = $this->syllabusService->create($request->validated(), auth()->user());

        if ($request->input('status') === Syllabus::STATUS_SUBMITTED) {
            try {
                $this->workflowService->submit($syllabus, auth()->user());
                return redirect()->route('syllabi.index')
                    ->with('success', 'Syllabus created and submitted for review.');
            } catch (\Exception $e) {
                return redirect()->route('syllabi.edit', $syllabus)
                    ->with('warning', 'Syllabus saved as draft, but automatic submission failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('syllabi.edit', $syllabus)
            ->with('success', 'Syllabus created successfully.');
    }

    public function show(Syllabus $syllabus)
    {
        if (!$this->syllabusService->canView($syllabus, auth()->user())) {
            abort(403);
        }

        $syllabus->load(['creator', 'departments', 'approver', 'reviews.reviewer']);

        $canEdit = $this->syllabusService->canEdit($syllabus, auth()->user());

        return view('syllabi.show', compact('syllabus', 'canEdit'));
    }

    public function edit(Syllabus $syllabus)
    {
        if (!$this->syllabusService->canEdit($syllabus, auth()->user())) {
            abort(403);
        }

        // Provide a plain array version of the syllabus (with departments)
        // for the Alpine.js form to hydrate safely.
        $syllabus->load('departments');
        $syllabusData = $syllabus->toArray();

        $programmesMetadata = Programme::where('status', Programme::STATUS_ACTIVE)
            ->get(['code', 'scheme_type'])
            ->mapWithKeys(fn ($programme) => [
                $programme->code => Programme::normalizeSchemeType($programme->scheme_type),
            ])
            ->toArray();

        return view('syllabi.edit', compact('syllabus', 'syllabusData', 'programmesMetadata'));
    }

    public function update(UpdateSyllabusRequest $request, Syllabus $syllabus)
    {
        $this->syllabusService->update($syllabus, $request->validated(), auth()->user());

        if ($request->input('status') === Syllabus::STATUS_SUBMITTED) {
            try {
                $this->workflowService->submit($syllabus, auth()->user());
                return redirect()->route('syllabi.index')
                    ->with('success', 'Syllabus updated and submitted for review.');
            } catch (\Exception $e) {
                return redirect()->route('syllabi.edit', $syllabus)
                    ->with('warning', 'Syllabus changes saved as draft, but submission failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('syllabi.edit', $syllabus)
            ->with('success', 'Syllabus updated successfully.');
    }

    public function destroy(Syllabus $syllabus)
    {
        if (!auth()->user()->isAdmin() && $syllabus->submitted_by !== auth()->id()) {
            abort(403);
        }

        if (!$syllabus->isDraft()) {
            return redirect()->back()
                ->with('error', 'Only draft syllabi can be deleted.');
        }

        $syllabus->delete();

        return redirect()->route('syllabi.index')
            ->with('success', 'Syllabus deleted successfully.');
    }

    public function clone(Syllabus $syllabus)
    {
        if ($syllabus->submitted_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        if (!$syllabus->isApproved()) {
            return redirect()->back()
                ->with('error', 'Only approved syllabi can be cloned.');
        }

        $clone = $this->syllabusService->clone($syllabus, auth()->user());

        return redirect()->route('syllabi.edit', $clone)
            ->with('success', 'Syllabus cloned successfully. Please update the semester and year.');
    }

    public function submit(Request $request, Syllabus $syllabus)
    {
        if ($syllabus->submitted_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        try {
            $this->workflowService->submit($syllabus, auth()->user());
            return redirect()->route('syllabi.index')
                ->with('success', 'Syllabus submitted for review.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, Syllabus $syllabus)
    {
        if (!auth()->user()->isApprover() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:approve,request_changes',
            'comments' => 'nullable|string|max:1000',
        ]);

        try {
            if ($request->action === 'approve') {
                $this->workflowService->approve($syllabus, auth()->user(), $request->comments);
                $message = 'Syllabus approved successfully.';
            } else {
                $this->workflowService->requestChanges($syllabus, auth()->user(), $request->comments ?: 'Please review and make necessary changes.');
                $message = 'Changes requested successfully.';
            }

            return redirect()->route('dashboard')
                ->with('success', $message);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Syllabus $syllabus)
    {
        if (!auth()->user()->isApprover() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|min:20|max:2000',
        ]);

        try {
            $this->workflowService->reject($syllabus, auth()->user(), $request->reason);
            return redirect()->route('dashboard')
                ->with('success', 'Syllabus rejected with feedback.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function history(Syllabus $syllabus)
    {
        if (!$this->syllabusService->canView($syllabus, auth()->user())) {
            abort(403);
        }

        $syllabus->load(['versions.creator', 'reviews.reviewer']);

        return view('syllabi.history', compact('syllabus'));
    }

    public function downloadPdf(Syllabus $syllabus)
    {
        if (!$syllabus->isApproved()) {
            abort(403, 'Only approved syllabi can be downloaded.');
        }

        if (!$this->syllabusService->canView($syllabus, auth()->user())) {
            abort(403);
        }

        return $this->pdfService->download($syllabus);
    }

    public function downloadDocx(Syllabus $syllabus)
    {
        if (!$syllabus->isApproved()) {
            abort(403, 'Only approved syllabi can be downloaded.');
        }

        if (!$this->syllabusService->canView($syllabus, auth()->user())) {
            abort(403);
        }

        return $this->docxService->download($syllabus);
    }
}




