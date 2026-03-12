<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            // Teaching Scheme additions
            $table->integer('slh_hours')->nullable()->after('iks_hours');  // Self-Learning Hours
            $table->integer('nlh_hours')->nullable()->after('slh_hours');  // Non-Lecture Hours
            
            // Examination Scheme additions
            $table->integer('tw_marks')->nullable()->after('examination_scheme');  // Term Work marks
            $table->boolean('is_internal_practical')->default(false)->after('tw_marks');  // @ indicator
            
            // Report Format for Level 4
            $table->json('report_format')->nullable()->after('training_schedule');
            
            // Question Paper Profile (new section)
            $table->json('question_paper_profile')->nullable()->after('mapping_matrix');
            
            // Certification data
            $table->json('certification_signatures')->nullable()->after('question_paper_profile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropColumn([
                'slh_hours',
                'nlh_hours',
                'tw_marks',
                'is_internal_practical',
                'report_format',
                'question_paper_profile',
                'certification_signatures',
            ]);
        });
    }
};
