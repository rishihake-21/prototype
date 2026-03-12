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
        Schema::create('course_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('faculty_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete(); // HOD (or admin)
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('deadline')->nullable();
            $table->string('term', 20)->nullable();
            $table->string('academic_year', 10)->nullable();
            $table->enum('status', ['pending', 'in_progress', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            // Ensure faculty belongs to department (pivot is primary key (user_id, department_id))
            $table->foreign(['faculty_user_id', 'department_id'])
                ->references(['user_id', 'department_id'])
                ->on('user_departments')
                ->cascadeOnDelete();

            $table->unique(['course_id', 'department_id', 'faculty_user_id', 'academic_year'], 'assignment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_assignments');
    }
};
