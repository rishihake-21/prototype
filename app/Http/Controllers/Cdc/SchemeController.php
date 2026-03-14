<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Scheme;
use App\Models\SchemeLevel;

class SchemeController extends Controller
{
    public function index()
    {
        $schemes = Scheme::withCount('programmes')->latest()->get();
        return view('cdc.schemes.index', compact('schemes'));
    }

    public function create()
    {
        return view('cdc.schemes.create', ['scheme' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schemes,name',
            'implemented_year' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'scheme_structure' => 'nullable|array',
            'scheme_structure.*.name' => 'required|string|max:100',
            'scheme_structure.*.children' => 'nullable|array',
            'scheme_structure.*.children.*.name' => 'required|string|max:100',
            'scheme_structure.*.children.*.columns' => 'nullable|array',
            'scheme_structure.*.children.*.columns.*' => 'string|max:100',
            'levels' => 'required|array|min:1',
            'levels.*.level_code' => 'required|string|max:20',
            'levels.*.level_name' => 'required|string|max:255',
            'levels.*.sort_order' => 'required|integer',
        ]);

        $scheme = Scheme::create([
            'name' => $validated['name'],
            'implemented_year' => $validated['implemented_year'],
            'description' => $validated['description'],
            'is_active' => $request->has('is_active'),
        ]);

        foreach ($request->input('levels', []) as $levelData) {
            $scheme->levels()->create($levelData);
        }

        if ($request->has('scheme_structure')) {
            $displayOrder = 0;
            foreach ($request->input('scheme_structure') as $l1Index => $l1Data) {
                if (trim($l1Data['name']) === '') continue;
                
                $l1Node = $scheme->assessmentComponents()->create([
                    'component_code' => \Illuminate\Support\Str::slug($l1Data['name']),
                    'component_name' => $l1Data['name'],
                    'display_order' => $displayOrder++,
                ]);
                
                if (!empty($l1Data['children'])) {
                    foreach ($l1Data['children'] as $l2Index => $l2Data) {
                        if (trim($l2Data['name']) === '') continue;
                        $l2Node = $scheme->assessmentComponents()->create([
                            'parent_id' => $l1Node->id,
                            'component_code' => \Illuminate\Support\Str::slug($l1Data['name'] . ' ' . $l2Data['name']),
                            'component_name' => $l2Data['name'],
                            'display_order' => $displayOrder++,
                        ]);

                        if (!empty($l2Data['columns'])) {
                            foreach ($l2Data['columns'] as $colName) {
                                if (trim($colName) === '') continue;
                                $scheme->assessmentComponents()->create([
                                    'parent_id' => $l2Node->id,
                                    'component_code' => \Illuminate\Support\Str::slug($l1Data['name'] . ' ' . $l2Data['name'] . ' ' . $colName),
                                    'component_name' => $colName,
                                    'display_order' => $displayOrder++,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('cdc.schemes.index')->with('success', 'Scheme created successfully.');
    }

    public function edit(Scheme $scheme)
    {
        $scheme->load(['levels', 'assessmentComponents']);
        return view('cdc.schemes.create', compact('scheme'));
    }

    public function update(Request $request, Scheme $scheme)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schemes,name,' . $scheme->id,
            'implemented_year' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'scheme_structure' => 'nullable|array',
            'scheme_structure.*.name' => 'required|string|max:100',
            'scheme_structure.*.children' => 'nullable|array',
            'scheme_structure.*.children.*.name' => 'required|string|max:100',
            'scheme_structure.*.children.*.columns' => 'nullable|array',
            'scheme_structure.*.children.*.columns.*' => 'string|max:100',
            'levels' => 'required|array|min:1',
            'levels.*.id' => 'nullable|exists:scheme_levels,id',
            'levels.*.level_code' => 'required|string|max:20',
            'levels.*.level_name' => 'required|string|max:255',
            'levels.*.sort_order' => 'required|integer',
        ]);

        $scheme->update([
            'name' => $validated['name'],
            'implemented_year' => $validated['implemented_year'],
            'description' => $validated['description'],
            'is_active' => $request->has('is_active'),
        ]);

        $inputLevels = $request->input('levels', []);
        $existingLevelIds = $scheme->levels()->pluck('id')->toArray();
        $keptIds = [];

        foreach ($inputLevels as $levelData) {
            if (!empty($levelData['id'])) {
                $level = SchemeLevel::where('scheme_id', $scheme->id)->find($levelData['id']);
                if ($level) {
                    $level->update($levelData);
                    $keptIds[] = $level->id;
                }
            }
            else {
                $level = $scheme->levels()->create($levelData);
                $keptIds[] = $level->id;
            }
        }

        $toDelete = array_diff($existingLevelIds, $keptIds);
        if (!empty($toDelete)) {
            SchemeLevel::whereIn('id', $toDelete)->delete();
        }
        // Sync Assessment Components
        // Deleting and re-inserting handles changes easily, though we could sync to keep IDs.
        $scheme->assessmentComponents()->delete();
        if ($request->has('scheme_structure')) {
            $displayOrder = 0;
            foreach ($request->input('scheme_structure') as $l1Index => $l1Data) {
                if (trim($l1Data['name']) === '') continue;
                
                $l1Node = $scheme->assessmentComponents()->create([
                    'component_code' => \Illuminate\Support\Str::slug($l1Data['name']),
                    'component_name' => $l1Data['name'],
                    'display_order' => $displayOrder++,
                ]);
                
                if (!empty($l1Data['children'])) {
                    foreach ($l1Data['children'] as $l2Index => $l2Data) {
                        if (trim($l2Data['name']) === '') continue;
                        $l2Node = $scheme->assessmentComponents()->create([
                            'parent_id' => $l1Node->id,
                            'component_code' => \Illuminate\Support\Str::slug($l1Data['name'] . ' ' . $l2Data['name']),
                            'component_name' => $l2Data['name'],
                            'display_order' => $displayOrder++,
                        ]);

                        if (!empty($l2Data['columns'])) {
                            foreach ($l2Data['columns'] as $colName) {
                                if (trim($colName) === '') continue;
                                $scheme->assessmentComponents()->create([
                                    'parent_id' => $l2Node->id,
                                    'component_code' => \Illuminate\Support\Str::slug($l1Data['name'] . ' ' . $l2Data['name'] . ' ' . $colName),
                                    'component_name' => $colName,
                                    'display_order' => $displayOrder++,
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return redirect()->route('cdc.schemes.index')->with('success', 'Scheme updated successfully.');
    }

    public function destroy(Scheme $scheme)
    {
        if ($scheme->programmes()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete scheme because it is being used by programmes.');
        }

        $scheme->delete();
        return redirect()->route('cdc.schemes.index')->with('success', 'Scheme deleted successfully.');
    }

    public function apiLeafComponents(Scheme $scheme)
    {
        return response()->json($scheme->getLeafColumns());
    }
}
