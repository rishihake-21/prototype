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
            $table->string('usage_scope')->nullable()->after('type');
            $table->string('semantic_key')->nullable()->after('usage_scope');
        });

        DB::table('scheme_assessment_components')
            ->orderBy('id')
            ->get(['id', 'component_name', 'value_kind', 'is_input', 'contributes_to_total'])
            ->each(function ($row) {
                $name = strtolower(trim((string) $row->component_name));
                $semanticKey = null;
                $usageScope = null;

                if (($row->value_kind ?? null) === 'group') {
                    $usageScope = 'display_only';
                } elseif (($row->value_kind ?? null) === 'duration') {
                    $semanticKey = 'paper_duration';
                    $usageScope = 'course_definition';
                } elseif (($row->value_kind ?? null) === 'marks') {
                    $usageScope = ($row->is_input && $row->contributes_to_total) ? 'course_definition' : 'display_only';

                    if (str_contains($name, 'fa-th')) {
                        $semanticKey = str_contains($name, 'min') ? 'fa_th_min' : 'fa_th_max';
                    } elseif (str_contains($name, 'sa-th')) {
                        $semanticKey = str_contains($name, 'min') ? 'sa_th_min' : 'sa_th_max';
                    } elseif (str_contains($name, 'fa-pr')) {
                        $semanticKey = str_contains($name, 'min') ? 'fa_pr_min' : 'fa_pr_max';
                    } elseif (str_contains($name, 'sa-pr')) {
                        $semanticKey = str_contains($name, 'min') ? 'sa_pr_min' : 'sa_pr_max';
                    } elseif (str_contains($name, 'sla')) {
                        $semanticKey = str_contains($name, 'min') ? 'sla_min' : 'sla_max';
                    } elseif (str_contains($name, 'oral') || preg_match('/\bor\b/', $name)) {
                        $semanticKey = str_contains($name, 'min') ? 'oral_min' : 'oral_max';
                    } elseif (str_contains($name, 'tw')) {
                        $semanticKey = str_contains($name, 'min') ? 'tw_min' : 'tw_max';
                    } elseif (str_contains($name, 'total')) {
                        $semanticKey = 'total_marks';
                    }
                }

                DB::table('scheme_assessment_components')
                    ->where('id', $row->id)
                    ->update([
                        'usage_scope' => $usageScope,
                        'semantic_key' => $semanticKey,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('scheme_assessment_components', function (Blueprint $table) {
            $table->dropColumn(['usage_scope', 'semantic_key']);
        });
    }
};
