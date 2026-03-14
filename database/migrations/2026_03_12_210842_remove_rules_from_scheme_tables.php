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
        Schema::dropIfExists('scheme_level_rules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('scheme_level_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_level_id')->constrained('scheme_levels')->cascadeOnDelete();
            $table->enum('course_type', ['compulsory', 'elective', 'audit'])->default('compulsory');
            $table->integer('courses_offered')->default(0);
            $table->integer('courses_to_complete')->default(0);
            $table->timestamps();

            // Prevent multiple rules of the same type for a single level
            $table->unique(['scheme_level_id', 'course_type']);
        });
    }
};
