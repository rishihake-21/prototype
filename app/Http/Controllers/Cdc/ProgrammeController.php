<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\ProgrammeLevel;
use App\Models\ProgrammeStructure;
use Illuminate\Http\Request;

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
            'code'          => 'required|string|max:10',
            'academic_year' => 'required|string|max:10',
            'scheme_id'     => 'required|exists:schemes,id',
            'status'        => 'required|in:draft,active,archived',
            'department_id' => 'required|exists:departments,id',
            'description'   => 'nullable|string|max:2000',
        ]);

        $validated['submitted_by'] = auth()->id();
        $validated['scheme_type'] = 'standard'; // Keep default or completely remove later

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

        return redirect()
            ->route('cdc.programmes.show', $programme)
            ->with('success', 'Programme created successfully.');
    }

    public function show(Programme $programme)
    {
        $programme->load(['levels.structure', 'creator']);
        return view('cdc.programmes.show', compact('programme'));
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
            'code'          => 'required|string|max:10',
            'academic_year' => 'required|string|max:10',
            'scheme_id'     => 'required|exists:schemes,id',
            'status'        => 'required|in:draft,active,archived',
            'department_id' => 'required|exists:departments,id',
            'description'   => 'nullable|string|max:2000',
        ]);

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
}
