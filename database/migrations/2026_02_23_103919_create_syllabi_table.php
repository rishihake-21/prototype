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
        Schema::create('syllabi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            
            $table->string('title');
            $table->string('course_code', 20);
            $table->text('course_description');
            $table->json('learning_outcomes');
            $table->text('prerequisites')->nullable();
            $table->tinyInteger('credits');
            $table->tinyInteger('duration_weeks');
            $table->string('instructor_name');
            $table->string('instructor_email');
            $table->enum('level', ['undergraduate', 'graduate', 'doctoral', 'certificate']);
            $table->enum('semester', ['Fall', 'Spring', 'Summer', 'Winter']);
            $table->year('year');
            $table->text('objectives');
            $table->json('topics');
            $table->json('assessments');
            $table->json('resources')->nullable();
            $table->text('grading_policy');
            $table->text('policies');
            
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'archived'])->default('draft');
            $table->tinyInteger('version_number')->default(1);
            $table->text('rejection_reason')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabi');
    }
};
