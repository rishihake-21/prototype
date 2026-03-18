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
        // Load courses (non-deleted, sorted by level then code)
        $courses = $programme->courses()
            ->with('level')
            ->whereNull('deleted_at')
            ->orderBy('level_id')
            ->orderBy('course_code')
            ->get();

        // Load current assignments from the single programme-wide sample path.
        // Older 10+/12+/Lateral rows are still read as fallback compatibility data.
        $assigned = SamplePath::where('programme_id', $programme->id)
            ->whereIn('entry_level', SamplePath::canonicalEntryLevels())
            ->get()
            ->groupBy('term_number')  // [term => Collection of SamplePath]
            ->map(fn($paths) => $paths->pluck('course_id')->unique()->flip()); // [term => {course_id => 0}]

        $terms = range(1, 6);

        return view('cdc.sample_path.index', compact(
            'programme', 'courses', 'assigned', 'terms'
        ));
    }

    /**
     * Save the entire grid (full replace per entry_level).
     */
    public function update(Request $request, Programme $programme)
    {
        $request->validate([
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

        // Delete the full programme-wide sample path, including legacy entry buckets.
        SamplePath::where('programme_id', $programme->id)
            ->whereIn('entry_level', SamplePath::canonicalEntryLevels())
            ->delete();

        foreach ($request->input('terms', []) as $term => $courseIds) {
            if (!is_array($courseIds)) {
                continue;
            }

            foreach ($courseIds as $courseId) {
                SamplePath::create([
                    'programme_id' => $programme->id,
                    'entry_level'  => SamplePath::ENTRY_LEVEL_STANDARD,
                    'term_number'  => (int) $term,
                    'course_id'    => (int) $courseId,
                ]);
            }
        }

        return redirect()
            ->route('cdc.programmes.sample-path', ['programme' => $programme])
            ->with('success', 'Sample path saved successfully.');
    }
}
