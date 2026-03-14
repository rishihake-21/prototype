<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheme_assessment_components', function (Blueprint $table) {
            $table->string('value_kind')->nullable()->after('type'); // marks|duration|group
            $table->boolean('is_input')->default(false)->after('value_kind');
            $table->boolean('contributes_to_total')->default(false)->after('is_input');
        });

        // Backfill sensible defaults for existing data.
        $all = DB::table('scheme_assessment_components')
            ->select('id', 'scheme_id', 'parent_id', 'component_name')
            ->orderBy('scheme_id')
            ->orderBy('display_order')
            ->get();

        if ($all->isEmpty()) {
            return;
        }

        $childrenByParent = [];
        foreach ($all as $row) {
            $pid = $row->parent_id === null ? null : (int) $row->parent_id;
            if (!isset($childrenByParent[$pid])) {
                $childrenByParent[$pid] = [];
            }
            $childrenByParent[$pid][] = (int) $row->id;
        }

        $isLeaf = function (int $id) use ($childrenByParent): bool {
            return empty($childrenByParent[$id] ?? []);
        };

        foreach ($all as $row) {
            $id = (int) $row->id;
            $name = strtolower(trim((string) $row->component_name));

            $valueKind = 'group';
            $isInput = false;
            $contrib = false;

            if ($isLeaf($id)) {
                // Duration-like fields (should not be summed into total marks).
                if ($name === 'paper duration' || str_contains($name, 'duration') || str_contains($name, 'hrs')) {
                    $valueKind = 'duration';
                    $isInput = true;
                    $contrib = false;
                } else {
                    $valueKind = 'marks';

                    // Mark-input leaves that contribute to total.
                    $isMax = str_contains($name, '(max)') || str_starts_with($name, 'fa-') || str_starts_with($name, 'sa-');
                    $isSlaMax = str_starts_with($name, 'max') && str_contains($name, 'sla') && !str_contains($name, 'min');

                    $isTotalOrMin = str_contains($name, 'total') || str_contains($name, 'min');

                    $isInput = ($isMax || $isSlaMax) && !$isTotalOrMin;
                    $contrib = $isInput;
                }
            }

            DB::table('scheme_assessment_components')
                ->where('id', $id)
                ->update([
                    'value_kind' => $valueKind,
                    'is_input' => $isInput ? 1 : 0,
                    'contributes_to_total' => $contrib ? 1 : 0,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('scheme_assessment_components', function (Blueprint $table) {
            $table->dropColumn(['value_kind', 'is_input', 'contributes_to_total']);
        });
    }
};

