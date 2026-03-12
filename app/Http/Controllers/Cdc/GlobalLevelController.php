<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GlobalLevelController extends Controller
{
    public function index()
    {
        $levels = \App\Models\GlobalLevel::orderBy('sort_order')->get();
        return view('cdc.global_levels.index', compact('levels'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'levels' => 'required|array|min:1',
            'levels.*.id' => 'nullable|exists:global_levels,id',
            'levels.*.level_code' => 'required|string|max:20',
            'levels.*.level_name' => 'required|string|max:255',
            'levels.*.sort_order' => 'required|integer',
        ]);

        $inputLevels = collect($request->input('levels', []));
        $existingLevelIds = \App\Models\GlobalLevel::pluck('id')->toArray();
        $keptIds = [];

        foreach ($inputLevels as $levelData) {
            if (!empty($levelData['id'])) {
                $level = \App\Models\GlobalLevel::find($levelData['id']);
                if ($level) {
                    $level->update($levelData);
                    $keptIds[] = $level->id;
                }
            } else {
                $newLevel = \App\Models\GlobalLevel::create($levelData);
                $keptIds[] = $newLevel->id;
            }
        }

        $toDelete = array_diff($existingLevelIds, $keptIds);
        if (!empty($toDelete)) {
             \App\Models\GlobalLevel::whereIn('id', $toDelete)->delete();
        }

        return redirect()->route('cdc.global-levels.index')->with('success', 'Global Levels updated successfully.');
    }
}
