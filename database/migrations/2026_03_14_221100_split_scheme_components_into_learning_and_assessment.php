<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Move all non-"Assessment Scheme" top-level trees from scheme_assessment_components
        // into scheme_learning_components. This preserves assessment component IDs for
        // existing course_assessments references.
        $schemes = DB::table('schemes')->select('id')->get();

        foreach ($schemes as $scheme) {
            $schemeId = (int) $scheme->id;

            $all = DB::table('scheme_assessment_components')
                ->where('scheme_id', $schemeId)
                ->orderBy('display_order')
                ->get();

            if ($all->isEmpty()) {
                continue;
            }

            $byId = [];
            $childrenByParent = [];
            foreach ($all as $row) {
                $byId[(int) $row->id] = $row;
                $pid = $row->parent_id === null ? null : (int) $row->parent_id;
                if (!isset($childrenByParent[$pid])) {
                    $childrenByParent[$pid] = [];
                }
                $childrenByParent[$pid][] = (int) $row->id;
            }

            // Find assessment root(s) by L1 name match.
            $assessmentRootIds = [];
            if (isset($childrenByParent[null])) {
                foreach ($childrenByParent[null] as $id) {
                    $name = strtolower(trim((string) $byId[$id]->component_name));
                    if ($name === 'assessment scheme') {
                        $assessmentRootIds[] = $id;
                    }
                }
            }

            $keepAssessmentIds = [];
            $stack = $assessmentRootIds;
            while (!empty($stack)) {
                $id = array_pop($stack);
                if (isset($keepAssessmentIds[$id])) {
                    continue;
                }
                $keepAssessmentIds[$id] = true;
                $kids = $childrenByParent[$id] ?? [];
                foreach ($kids as $kid) {
                    $stack[] = $kid;
                }
            }

            // Nothing to move if everything is assessment or no assessment root exists.
            $moveIds = [];
            foreach ($byId as $id => $_row) {
                if (!isset($keepAssessmentIds[$id])) {
                    $moveIds[$id] = true;
                }
            }
            if (empty($moveIds)) {
                continue;
            }

            // Insert moved rows into scheme_learning_components while preserving hierarchy
            // with a new id mapping (old assessment id -> new learning id).
            $idMap = [];
            $pending = array_keys($moveIds);

            // Pre-compute depth so parents are inserted before children.
            $depth = [];
            $getDepth = function (int $id) use (&$getDepth, &$depth, $byId, $moveIds): int {
                if (isset($depth[$id])) {
                    return $depth[$id];
                }
                $p = $byId[$id]->parent_id;
                if ($p === null) {
                    return $depth[$id] = 0;
                }
                $pid = (int) $p;
                if (!isset($moveIds[$pid])) {
                    // If parent isn't moved, treat as root in learning.
                    return $depth[$id] = 0;
                }
                return $depth[$id] = 1 + $getDepth($pid);
            };

            usort($pending, function ($a, $b) use ($getDepth, $byId) {
                $da = $getDepth((int) $a);
                $db = $getDepth((int) $b);
                if ($da !== $db) return $da <=> $db;
                return ((int) $byId[(int) $a]->display_order) <=> ((int) $byId[(int) $b]->display_order);
            });

            foreach ($pending as $oldId) {
                $row = $byId[(int) $oldId];
                $oldParent = $row->parent_id === null ? null : (int) $row->parent_id;
                $newParent = null;
                if ($oldParent !== null && isset($moveIds[$oldParent])) {
                    $newParent = $idMap[$oldParent] ?? null;
                }

                $newId = DB::table('scheme_learning_components')->insertGetId([
                    'scheme_id' => $schemeId,
                    'parent_id' => $newParent,
                    'component_code' => $row->component_code,
                    'component_name' => $row->component_name,
                    'type' => $row->type,
                    'display_order' => (int) $row->display_order,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);

                $idMap[(int) $oldId] = (int) $newId;
            }

            // Delete moved rows from scheme_assessment_components.
            DB::table('scheme_assessment_components')
                ->where('scheme_id', $schemeId)
                ->whereIn('id', array_keys($moveIds))
                ->delete();
        }
    }

    public function down(): void
    {
        // No automatic rollback. The split is lossy in terms of IDs.
    }
};

