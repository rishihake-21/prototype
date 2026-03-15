<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\SamplePath;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SamplePathController extends Controller
{
    /**
     * Show the term-wise distribution grid.
     */
    public function index(Request $request, Programme $programme)
    {
        $entryLevel = $request->input('entry_level', '10+');
        $entryLevels = ['10+', '12+', 'Lateral'];

        // Load courses (non-deleted, sorted by level then code)
        $courses = $programme->courses()
            ->with('level')
            ->whereNull('deleted_at')
            ->orderBy('level_id')
            ->orderBy('course_code')
            ->get();

        // Load current assignments for this entry level
        $assigned = SamplePath::where('programme_id', $programme->id)
            ->where('entry_level', $entryLevel)
            ->get()
            ->groupBy('term_number')  // [term => Collection of SamplePath]
            ->map(fn($paths) => $paths->pluck('course_id')->flip()); // [term => {course_id => 0}]

        $terms = range(1, 6);

        return view('cdc.sample_path.index', compact(
            'programme', 'courses', 'assigned', 'terms', 'entryLevel', 'entryLevels'
        ));
    }

    /**
     * Save the entire grid (full replace per entry_level).
     */
    public function update(Request $request, Programme $programme)
    {
        $request->validate([
            'entry_level' => 'required|string|max:20',
            'terms'       => 'nullable|array',
            'terms.*'     => 'array',
            'terms.*.*'   => [
                'integer',
                Rule::exists('courses', 'id')->where(function ($query) use ($programme) {
                    $query->where('programme_id', $programme->id)
                        ->whereNull('deleted_at');
                }),
            ],
        ]);

        $entryLevel = $request->input('entry_level');

        // Delete existing for this entry level
        SamplePath::where('programme_id', $programme->id)
            ->where('entry_level', $entryLevel)
            ->delete();

        // Re-insert
        $assignments = $request->input('assignments', []);
        // assignments is sent as "term_N[course_id]" -> we receive it as
        // assignments[term_N] = [course_id1, course_id2, ...]
        foreach ($request->input('terms', []) as $term => $courseIds) {
            if (!is_array($courseIds)) continue;
            foreach ($courseIds as $courseId) {
                SamplePath::create([
                    'programme_id' => $programme->id,
                    'entry_level'  => $entryLevel,
                    'term_number'  => (int) $term,
                    'course_id'    => (int) $courseId,
                ]);
            }
        }

        return redirect()
            ->route('cdc.programmes.sample-path', ['programme' => $programme, 'entry_level' => $entryLevel])
            ->with('success', 'Sample path saved successfully.');
    }
}
