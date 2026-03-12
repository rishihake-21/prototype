<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            if (!Schema::hasColumn('syllabi', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('course_code')->constrained('courses')->nullOnDelete();
            }
        });

        // Best-effort backfill: if course_code matches exactly one course, set course_id.
        if (Schema::hasColumn('syllabi', 'course_code') && Schema::hasColumn('courses', 'course_code')) {
            $syllabi = DB::table('syllabi')->whereNotNull('course_code')->get(['id', 'course_code']);
            foreach ($syllabi as $s) {
                $matches = DB::table('courses')->where('course_code', $s->course_code)->pluck('id');
                if ($matches->count() === 1) {
                    DB::table('syllabi')->where('id', $s->id)->update(['course_id' => $matches->first()]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            if (Schema::hasColumn('syllabi', 'course_id')) {
                $table->dropConstrainedForeignId('course_id');
            }
        });
    }
};
