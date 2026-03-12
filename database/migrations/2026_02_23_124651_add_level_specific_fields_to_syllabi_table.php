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
            // Programme and Course Identity
            $table->string('program_name', 10)->nullable()->after('department_id');
            $table->string('academic_year', 10)->nullable()->after('course_code');
            $table->integer('iks_hours')->default(0)->after('academic_year');
            
            // Level-specific flags
            $table->boolean('is_online_exam')->default(false)->after('iks_hours');
            $table->string('elective_group', 20)->nullable()->after('is_online_exam');
            $table->boolean('is_part_of_group')->default(false)->after('elective_group');
            $table->string('training_location', 50)->nullable()->after('is_part_of_group');
            
            // Teaching & Examination Scheme (JSON for flexibility)
            $table->json('teaching_scheme')->nullable()->after('training_location');
            $table->json('examination_scheme')->nullable()->after('teaching_scheme');
            
            // Narrative Sections
            $table->text('rationale')->nullable()->after('course_description');
            $table->json('course_objectives')->nullable()->after('rationale');
            $table->json('course_outcomes')->nullable()->after('course_objectives');
            
            // Level-specific Content
            $table->json('units')->nullable()->after('topics');
            $table->json('specification_table')->nullable()->after('units');
            $table->json('practical_tasks')->nullable()->after('specification_table');
            $table->json('training_schedule')->nullable()->after('practical_tasks');
            
            // Project Specifics (Level 4)
            $table->string('project_phase', 20)->nullable()->after('training_schedule');
            $table->integer('group_size_min')->nullable()->after('project_phase');
            $table->integer('group_size_max')->nullable()->after('group_size_min');
            $table->boolean('logbook_required')->default(false)->after('group_size_max');
            $table->boolean('industry_supervisor')->default(false)->after('logbook_required');
            
            // Learning Resources
            $table->json('books')->nullable()->after('resources');
            $table->json('software_websites')->nullable()->after('books');
            $table->json('equipment_list')->nullable()->after('software_websites');
            
            // CO-PO Mapping Matrix
            $table->json('mapping_matrix')->nullable()->after('equipment_list');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropColumn([
                'program_name',
                'academic_year',
                'iks_hours',
                'is_online_exam',
                'elective_group',
                'is_part_of_group',
                'training_location',
                'teaching_scheme',
                'examination_scheme',
                'rationale',
                'course_objectives',
                'course_outcomes',
                'units',
                'specification_table',
                'practical_tasks',
                'training_schedule',
                'project_phase',
                'group_size_min',
                'group_size_max',
                'logbook_required',
                'industry_supervisor',
                'books',
                'software_websites',
                'equipment_list',
                'mapping_matrix',
            ]);
        });
    }
};
