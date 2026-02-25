<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSyllabusRequest;
use App\Http\Requests\UpdateSyllabusRequest;
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

    public function create()
    {
        return view('syllabi.create');
    }

    public function store(StoreSyllabusRequest $request)
    {
        $syllabus = $this->syllabusService->create($request->validated(), auth()->user());

        return redirect()->route('syllabi.edit', $syllabus)
            ->with('success', 'Syllabus created successfully.');
    }

    public function show(Syllabus $syllabus)
    {
        if (!$this->syllabusService->canView($syllabus, auth()->user())) {
            abort(403);
        }

        $syllabus->load(['creator', 'departments', 'approver', 'reviews.reviewer']);

        return view('syllabi.show', compact('syllabus'));
    }

    public function edit(Syllabus $syllabus)
    {
        if (!$this->syllabusService->canEdit($syllabus, auth()->user())) {
            abort(403);
        }

        return view('syllabi.edit', compact('syllabus'));
    }

    public function update(UpdateSyllabusRequest $request, Syllabus $syllabus)
    {
        $this->syllabusService->update($syllabus, $request->validated(), auth()->user());

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
