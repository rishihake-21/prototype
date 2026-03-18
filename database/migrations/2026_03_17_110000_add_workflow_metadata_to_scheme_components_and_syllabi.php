<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheme_learning_components', function (Blueprint $table) {
            $table->string('usage_scope')->nullable()->after('type');
            $table->string('semantic_key')->nullable()->after('usage_scope');
            $table->string('value_kind')->nullable()->after('semantic_key');
            $table->string('entry_mode')->nullable()->after('value_kind');
            $table->string('total_role')->nullable()->after('entry_mode');
        });

        Schema::table('scheme_assessment_components', function (Blueprint $table) {
            $table->string('entry_mode')->nullable()->after('value_kind');
            $table->string('total_role')->nullable()->after('entry_mode');
        });

        Schema::table('syllabi', function (Blueprint $table) {
            $table->json('learning_scheme_rows')->nullable()->after('teaching_scheme');
            $table->json('learning_scheme_leaf_columns')->nullable()->after('learning_scheme_rows');
            $table->json('learning_scheme_values')->nullable()->after('learning_scheme_leaf_columns');
            $table->json('assessment_scheme_rows')->nullable()->after('examination_scheme');
            $table->json('assessment_scheme_leaf_columns')->nullable()->after('assessment_scheme_rows');
            $table->json('assessment_scheme_values')->nullable()->after('assessment_scheme_leaf_columns');
        });

        $learningRows = DB::table('scheme_learning_components')
            ->select('id', 'component_name', 'parent_id')
            ->orderBy('display_order')
            ->get();

        $childrenByParent = [];
        foreach ($learningRows as $row) {
            $pid = $row->parent_id === null ? null : (int) $row->parent_id;
            $childrenByParent[$pid] ??= [];
            $childrenByParent[$pid][] = (int) $row->id;
        }

        foreach ($learningRows as $row) {
            $id = (int) $row->id;
            $name = strtolower(trim((string) $row->component_name));
            $isLeaf = empty($childrenByParent[$id] ?? []);

            [$semanticKey, $valueKind, $usageScope] = match (true) {
                $name === 'credits' => ['credits', 'credits', 'syllabus'],
                $name === 'cl' || str_contains($name, 'classroom') => ['th_hours', 'hours', 'syllabus'],
                $name === 'tu' || str_contains($name, 'tutorial') => ['tu_hours', 'hours', 'syllabus'],
                $name === 'll' || $name === 'practical' || str_contains($name, 'laboratory') => ['pr_hours', 'hours', 'syllabus'],
                str_contains($name, 'self learning') || str_contains($name, 'slh') => ['slh_hours', 'hours', 'syllabus'],
                str_contains($name, 'notional') || str_contains($name, 'nlh') => ['nlh_hours', 'hours', 'syllabus'],
                str_contains($name, 'total learning') || str_contains($name, 'total hrs') => ['total_hours', 'hours', 'display_only'],
                default => [null, $isLeaf ? 'text' : 'group', $isLeaf ? 'display_only' : 'display_only'],
            };

            DB::table('scheme_learning_components')
                ->where('id', $id)
                ->update([
                    'semantic_key' => $semanticKey,
                    'value_kind' => $valueKind,
                    'usage_scope' => $usageScope,
                    'entry_mode' => $isLeaf ? (($usageScope === 'display_only') ? 'computed' : 'input') : 'readonly',
                    'total_role' => match ($semanticKey) {
                        'credits' => 'summary',
                        'total_hours', 'nlh_hours' => 'derived',
                        default => $isLeaf ? 'informational' : 'group',
                    },
                ]);
        }

        DB::table('scheme_assessment_components')
            ->orderBy('id')
            ->get(['id', 'semantic_key', 'usage_scope', 'value_kind', 'is_input', 'contributes_to_total'])
            ->each(function ($row) {
                $entryMode = ($row->usage_scope === 'display_only' || !$row->is_input) ? 'computed' : 'input';
                $totalRole = match (true) {
                    $row->semantic_key === 'total_marks' => 'derived',
                    is_string($row->semantic_key) && str_ends_with($row->semantic_key, '_min') => 'min_pass',
                    (bool) $row->contributes_to_total => 'adds_to_total',
                    ($row->value_kind === 'duration') => 'informational',
                    default => 'informational',
                };

                DB::table('scheme_assessment_components')
                    ->where('id', $row->id)
                    ->update([
                        'entry_mode' => $entryMode,
                        'total_role' => $totalRole,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropColumn([
                'learning_scheme_rows',
                'learning_scheme_leaf_columns',
                'learning_scheme_values',
                'assessment_scheme_rows',
                'assessment_scheme_leaf_columns',
                'assessment_scheme_values',
            ]);
        });

        Schema::table('scheme_assessment_components', function (Blueprint $table) {
            $table->dropColumn(['entry_mode', 'total_role']);
        });

        Schema::table('scheme_learning_components', function (Blueprint $table) {
            $table->dropColumn(['usage_scope', 'semantic_key', 'value_kind', 'entry_mode', 'total_role']);
        });
    }
};
