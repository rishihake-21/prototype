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
        Schema::create('course_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('scheme_assessment_components')->onDelete('cascade');
            $table->integer('max_marks')->default(0);
            $table->integer('min_marks')->default(0);
            $table->timestamps();
            
            $table->unique(['course_id', 'component_id']); // Ensure each component is only added once per course
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_assessments');
    }
};
