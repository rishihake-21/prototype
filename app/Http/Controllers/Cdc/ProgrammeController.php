<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\ProgrammeStructure;
use App\Models\SamplePath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProgrammeController extends Controller
{
    public function index()
    {
        $programmes = Programme::with('creator')
            ->latest()
            ->paginate(15);

        return view('cdc.programmes.index', compact('programmes'));
    }

    public function create()
    {
        return view('cdc.programmes.create', [
            'statuses'    => Programme::statuses(),
            'departments' => \App\Models\Department::all(),
            'schemes'     => \App\Models\Scheme::where('is_active', true)->orderBy('name')->get(),
            'programme'   => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:10|unique:programmes,code',
            'academic_year' => 'required|string|max:10',
            'scheme_id'     => 'required|exists:schemes,id',
            'status'        => 'required|in:draft,active,archived',
            'department_id' => 'required|exists:departments,id',
            'description'   => 'nullable|string|max:2000',
        ]);

        $validated['submitted_by'] = auth()->id();
        $validated['scheme_type'] = 'standard'; // Keep default or completely remove later

        $programme = DB::transaction(function () use ($validated) {
            $programme = Programme::create($validated);

            // Import from Selected Scheme
            $programme->load('scheme.levels');
            $schemeLevels = $programme->scheme->levels;

            foreach ($schemeLevels as $levelData) {
                $level = ProgrammeLevel::create([
                    'programme_id' => $programme->id,
                    'level_code'   => $levelData->level_code,
                    'level_name'   => $levelData->level_name,
                    'sort_order'   => $levelData->sort_order,
                ]);

                ProgrammeStructure::create([
                    'programme_id' => $programme->id,
                    'level_id'     => $level->id,
                ]);
            }

            return $programme;
        });

        return redirect()
            ->route('cdc.programmes.show', $programme)
            ->with('success', 'Programme created successfully.');
    }

    public function show(Programme $programme)
    {
        $programme->load(['levels.structure', 'creator']);
        return view('cdc.programmes.show', compact('programme'));
    }

    public function booklet(Programme $programme)
    {
        return view('cdc.programmes.booklet', $this->buildBookletData($programme));
    }

    public function downloadBookletPdf(Programme $programme)
    {
        $data = $this->buildBookletData($programme);

        $pdf = Pdf::loadView('pdf.programme-booklet', $data);
        $pdf->setPaper(config('sms.pdf_paper_size', 'A4'), 'landscape');

        $safeCode = preg_replace('/[^A-Za-z0-9_-]+/', '_', $programme->code);

        return $pdf->download("programme_booklet_{$safeCode}_{$programme->academic_year}.pdf");
    }

    public function edit(Programme $programme)
    {
        return view('cdc.programmes.create', [
            'programme'   => $programme,
            'departments' => \App\Models\Department::all(),
            'statuses'    => Programme::statuses(),
            'schemes'     => \App\Models\Scheme::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Programme $programme)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => [
                'required',
                'string',
                'max:10',
                Rule::unique('programmes', 'code')->ignore($programme->id),
            ],
            'academic_year' => 'required|string|max:10',
            'status'        => 'required|in:draft,active,archived',
            'department_id' => 'required|exists:departments,id',
            'description'   => 'nullable|string|max:2000',
        ]);

        if ((int) $request->input('scheme_id', $programme->scheme_id) !== (int) $programme->scheme_id) {
            return redirect()
                ->back()
                ->withErrors(['scheme_id' => 'The scheme cannot be changed after a programme is created.'])
                ->withInput();
        }

        $programme->update($validated);

        return redirect()
            ->route('cdc.programmes.show', $programme)
            ->with('success', 'Programme updated successfully.');
    }

    public function destroy(Programme $programme)
    {
        if (! $programme->isDraft()) {
            return redirect()->back()
                ->with('error', 'Only draft programmes can be deleted.');
        }

        $programme->delete();

        return redirect()
            ->route('cdc.programmes.index')
            ->with('success', 'Programme deleted.');
    }

    private function buildBookletData(Programme $programme): array
    {
        $programme->load([
            'department',
            'scheme',
            'creator',
            'levels.structure',
            'courses' => fn ($query) => $query->with(['level', 'departments', 'assessments.component'])->orderBy('course_code'),
            'awardClassCourses.course.level',
            'awardClassCourses.course.assessments.component',
        ]);

        $courses = $programme->courses;
        $samplePaths = $programme->samplePaths()
            ->with('course.level')
            ->orderBy('entry_level')
            ->orderBy('term_number')
            ->get();

        $electiveAssignments = CourseAssignment::with(['course.level', 'department'])
            ->where('academic_year', $programme->academic_year)
            ->whereHas('course', function ($query) use ($programme) {
                $query->where('programme_id', $programme->id)
                    ->where('course_type', Course::TYPE_ELECTIVE);
            })
            ->get();

        $assignmentsByCourse = $electiveAssignments->groupBy('course_id');
        $coursesByLevel = $courses->groupBy('level_id');

        $levelSections = $programme->levels->map(function (ProgrammeLevel $level) use ($programme, $coursesByLevel, $assignmentsByCourse) {
            $structure = $level->structure;

            $rows = $coursesByLevel->get($level->id, collect())
                ->sortBy(function (Course $course) {
                    $typeOrder = match ($course->course_type) {
                        Course::TYPE_COMPULSORY => 0,
                        Course::TYPE_ELECTIVE => 1,
                        default => 2,
                    };

                    return sprintf('%02d-%s-%s', $typeOrder, $course->elective_group ?? '', $course->course_code);
                })
                ->values()
                ->map(function (Course $course, int $index) use ($programme, $assignmentsByCourse) {
                    $courseAssignments = $assignmentsByCourse->get($course->id, collect());

                    $availability = $course->is_common_course
                        ? $course->departments->pluck('name')->filter()->unique()->sort()->values()
                        : collect([$programme->department?->name])->filter();

                    $selectedBy = $courseAssignments->pluck('department.name')->filter()->unique()->sort()->values();

                    return [
                        'index' => $index + 1,
                        'course' => $course,
                        'availability' => $availability,
                        'selected_by' => $selectedBy,
                        'assessment_marks' => $course->assessments->pluck('max_marks', 'component_id'),
                    ];
                });

            $electiveRows = $rows->filter(fn (array $row) => $row['course']->course_type === Course::TYPE_ELECTIVE)->values();
            $selectedElectiveRows = $electiveRows->filter(fn (array $row) => $row['selected_by']->isNotEmpty())->values();
            $compulsoryRows = $rows->filter(fn (array $row) => $row['course']->course_type === Course::TYPE_COMPULSORY)->values();
            $auditRows = $rows->filter(fn (array $row) => $row['course']->course_type === Course::TYPE_AUDIT)->values();

            return [
                'level' => $level,
                'structure' => $structure,
                'courses' => $rows,
                'compulsory_courses' => $compulsoryRows,
                'elective_courses' => $electiveRows,
                'audit_courses' => $auditRows,
                'defined_courses' => $rows->count(),
                'offered_courses' => (int) ($structure?->total_courses_offered ?? 0),
                'elective_pool_defined' => $electiveRows->count(),
                'elective_selected_count' => $selectedElectiveRows->count(),
                'elective_remaining' => max((int) ($structure?->elective_count ?? 0) - $selectedElectiveRows->count(), 0),
                'selected_elective_codes' => $selectedElectiveRows
                    ->map(fn (array $row) => $row['course']->course_code)
                    ->values(),
            ];
        })->values();

        $structureRows = $programme->levels->pluck('structure')->filter();

        $structureTotals = [
            'offered' => $structureRows->sum('total_courses_offered'),
            'to_complete' => $structureRows->sum('courses_to_complete'),
            'compulsory' => $structureRows->sum('compulsory_count'),
            'elective_offered' => $structureRows->sum('elective_offered_count'),
            'elective_to_complete' => $structureRows->sum('elective_count'),
            'th' => $structureRows->sum('th_hours'),
            'tu' => $structureRows->sum('tu_hours'),
            'pr' => $structureRows->sum('pr_hours'),
            'hours' => $structureRows->sum('total_hours'),
            'credits' => $structureRows->sum(fn ($row) => (float) $row->total_credits),
            'marks' => $structureRows->sum('total_marks'),
        ];

        $courseTotals = [
            'defined' => $courses->count(),
            'common' => $courses->where('is_common_course', true)->count(),
            'electives' => $courses->where('course_type', Course::TYPE_ELECTIVE)->count(),
            'credits' => $courses->sum(fn (Course $course) => (float) $course->credits),
            'marks' => $courses->sum('total_marks'),
        ];

        $samplePathSections = $samplePaths
            ->groupBy('term_number')
            ->sortKeys()
            ->map(function ($termRows, $termNumber) {
                $sorted = $termRows
                    ->sortBy(fn (SamplePath $path) => sprintf(
                        '%02d-%s-%s',
                        match ($path->course?->course_type) {
                            Course::TYPE_COMPULSORY => 0,
                            Course::TYPE_ELECTIVE => 1,
                            default => 2,
                        },
                        $path->course?->elective_group ?? '',
                        $path->course?->course_code ?? ''
                    ))
                    ->values();

                return [
                    'term_number' => (int) $termNumber,
                    'term_label' => SamplePath::termLabel((int) $termNumber),
                    'compulsory' => $sorted->filter(fn (SamplePath $path) => $path->course?->course_type === Course::TYPE_COMPULSORY)->values(),
                    'elective' => $sorted->filter(fn (SamplePath $path) => $path->course?->course_type === Course::TYPE_ELECTIVE)->values(),
                    'audit' => $sorted->filter(fn (SamplePath $path) => $path->course?->course_type === Course::TYPE_AUDIT)->values(),
                ];
            })
            ->values();

        $awardClassCourses = $programme->awardClassCourses
            ->filter(fn ($item) => $item->course !== null)
            ->sortBy('sort_order')
            ->values();

        return [
            'programme' => $programme,
            'institutionName' => config('sms.institution_name', 'Institution Name'),
            'levelSections' => $levelSections,
            'structureTotals' => $structureTotals,
            'courseTotals' => $courseTotals,
            'samplePathSections' => $samplePathSections,
            'awardClassCourses' => $awardClassCourses,
            'assessmentLeafColumns' => collect($programme->scheme?->getCourseAssessmentLeafColumns() ?? []),
        ];
    }
}
