<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheme;
use App\Models\Level;
use App\Models\SchemeLevel;
use App\Models\Course;

class SchemeController extends Controller
{

    /* -------------------------
       CREATE SCHEME PAGE
    --------------------------*/
    public function create()
    {
        $levels = Level::all();
        return view('scheme.create', compact('levels'));
    }

    /* -------------------------
       STORE SCHEME
    --------------------------*/
    public function store(Request $request)
    {
        $scheme = Scheme::create([
            'programme_name' => $request->programme_name,
            'programme_code' => $request->programme_code,
            'year' => $request->year,
        ]);

        foreach ($request->levels ?? [] as $data) {

            if (empty($data['level_name'])) {
                continue;
            }

            SchemeLevel::create([
                'scheme_id' => $scheme->id,
                'level_name' => $data['level_name'],
                'is_audit' => false,
                'courses_offered' => $data['courses_offered'] ?? 0,
                'th' => $data['th'] ?? 0,
                'tu' => $data['tu'] ?? 0,
                'pr' => $data['pr'] ?? 0,
                'total_hours' => $data['total_hours'] ?? 0,
                'total_credits' => $data['total_credits'] ?? 0,
                'marks' => $data['marks'] ?? 0,
            ]);
        }

// ---------- SAVE AUDIT AS LEVEL ----------
        if ($request->has('audit_enabled')) {

            SchemeLevel::create([
                'scheme_id' => $scheme->id,
                'level_name' => 'Audit Courses',
                'is_audit' => true,
                'courses_offered' => $request->audit_courses ?? 0,
                'th' => $request->audit_th ?? 0,
                'tu' => $request->audit_tu ?? 0,
                'pr' => $request->audit_pr ?? 0,
                'total_hours' => ($request->audit_th ?? 0)
                            + ($request->audit_tu ?? 0)
                            + ($request->audit_pr ?? 0),
                'total_credits' => 0,
                'marks' => 0,
            ]);
        }

    $firstLevel = SchemeLevel::where('scheme_id', $scheme->id)->first();

    if ($firstLevel) {
        return redirect()->route('scheme.addCourses', [
            'scheme' => $scheme->id,
            'level' => $firstLevel->id
        ]);
    }

    return redirect()->route('scheme.create');
    }

    /* -------------------------
       SHOW ADD COURSES PAGE
    --------------------------*/
public function addCourses($schemeId, $levelId)
{
    $scheme = Scheme::findOrFail($schemeId);

    $levels = SchemeLevel::where('scheme_id', $schemeId)
        ->orderBy('id')
        ->get();

    $schemeLevel = $levels->where('id', $levelId)->firstOrFail();

    // Find current level position
    $currentIndex = $levels->search(function ($level) use ($levelId) {
        return $level->id == $levelId;
    });

    $currentStep = $currentIndex + 1;
    $totalSteps = $levels->count();

    return view('scheme.add_courses', compact(
        'scheme',
        'schemeLevel',
        'currentStep',
        'totalSteps'
    ));
}

    /* -------------------------
       STORE COURSES
    --------------------------*/
public function storeCourses(Request $request, $schemeId, $levelId)
{
    $schemeLevel = SchemeLevel::where('scheme_id', $schemeId)
        ->where('id', $levelId)
        ->firstOrFail();

    $totalTH = 0;
    $totalTU = 0;
    $totalPR = 0;
    $totalCredits = 0;
    $totalMarks = 0;

    foreach ($request->courses ?? [] as $courseData) {

        $totalTH += $courseData['th'] ?? 0;
        $totalTU += $courseData['tu'] ?? 0;
        $totalPR += $courseData['pr'] ?? 0;
        if(!$schemeLevel->is_audit) {
            $totalCredits += $courseData['credits'] ?? 0;
            $totalMarks += $courseData['exam_total'] ?? 0;
        }
    }

    // ----------------------------
    // STRICT SERVER VALIDATION
    // ----------------------------
    if (
        $totalTH != $schemeLevel->th ||
        $totalTU != $schemeLevel->tu ||
        $totalPR != $schemeLevel->pr ||
        (!$schemeLevel->is_audit && $totalCredits != $schemeLevel->total_credits) ||
        (!$schemeLevel->is_audit && $totalMarks != $schemeLevel->marks)
    ) {
        return back()->withErrors('Totals must exactly match allowed limits.');
    }

    // ----------------------------
    // DELETE OLD COURSES (EDIT CASE)
    // ----------------------------
    Course::where('scheme_id', $schemeId)
        ->where('scheme_level_id', $schemeLevel->id)
        ->delete();

    // ----------------------------
    // SAVE COURSES
    // ----------------------------
    foreach ($request->courses ?? [] as $courseData) {

        Course::create([
            'scheme_id' => $schemeId,
            'scheme_level_id' => $schemeLevel->id,

            'course_code' => $courseData['course_code'],
            'course_title' => $courseData['course_title'],
            'Abbr' => $courseData['abbr'] ?? null,
            'year' => $courseData['year'] ?? null,
            'term' => $courseData['term'] ?? null,
            'th' => $courseData['th'] ?? 0,
            'tu' => $courseData['tu'] ?? 0,
            'pr' => $courseData['pr'] ?? 0,
            'total_hours' => $courseData['total_hours'] ?? 0,
            'credits' => $schemeLevel->is_audit ? 0 : ($courseData['credits'] ?? 0),

            'theory_hours' => $courseData['theory_hours'] ?? 0,
            'theory_marks' => $courseData['theory_marks'] ?? 0,
            'test_marks' => $courseData['test_marks'] ?? 0,
            'pr_marks' => $courseData['pr_marks'] ?? 0,
            'or_marks' => $courseData['or_marks'] ?? 0,
            'tw_marks' => $courseData['tw_marks'] ?? 0,

            'marks'   => $schemeLevel->is_audit ? 0 : ($courseData['exam_total'] ?? 0),
            'type' => $courseData['type'] ?? 'compulsory',
            'is_audit' => $schemeLevel->is_audit ? 1 : 0,
            'is_award' => isset($courseData['is_award']) ? 1 : 0,
        ]);
    }


    // ----------------------------
    // MOVE TO NEXT LEVEL
    // ----------------------------

    $levels = SchemeLevel::where('scheme_id', $schemeId)
        ->orderBy('id')
        ->get()
        ->values(); // important for index access

    $currentIndex = $levels->search(fn($level) => $level->id == $levelId);

    if ($currentIndex !== false && $currentIndex < $levels->count() - 1) {

        $nextLevel = $levels[$currentIndex + 1];

        return redirect()->route('scheme.addCourses', [
            'scheme' => $schemeId,
            'level'  => $nextLevel->id
        ])->with('success', 'Level Saved Successfully. Continue to next level.');
    }

    // ----------------------------
    // ALL LEVELS COMPLETED
    // ----------------------------

    return redirect()->route('scheme.summary', $schemeId)
        ->with('success', 'All Levels Completed Successfully.');
}

// ----------------------------
// SCHEME SUMMARY PAGE
// ----------------------------
public function summary($schemeId)
{
    $scheme = Scheme::findOrFail($schemeId);

    $levels = SchemeLevel::where('scheme_id', $schemeId)
        ->orderBy('id')
        ->get();

    // Get all courses
    $allCourses = Course::where('scheme_id', $schemeId)->get();

    // Group courses by level (for level tables)
    $courses = $allCourses->groupBy('scheme_level_id');

    return view('scheme.summary', compact(
        'scheme',
        'levels',
        'courses',
        'allCourses'
    ));
}

public function page18($schemeId)
{
    $scheme = Scheme::findOrFail($schemeId);

    $courses = Course::where('scheme_id', $schemeId)->get();

    // group by type
    $compulsory = $courses->where('type', 'compulsory');
    $elective = $courses->where('type', 'elective');
    $audit = $courses->where('type', 'audit');

    return view('scheme.page18', compact(
        'scheme',
        'courses',
        'compulsory',
        'elective',
        'audit'
    ));
}

}

