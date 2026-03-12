<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maps a common course to the departments that offer it.
     */
    public function up(): void
    {
        Schema::create('course_programme_departments', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->primary(['course_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_programme_departments');
    }
};
