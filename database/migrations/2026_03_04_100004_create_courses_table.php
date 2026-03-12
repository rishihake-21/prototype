<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('programme_levels')->cascadeOnDelete();

            // Identification
            $table->string('course_code', 20);
            $table->string('course_title', 255);
            $table->string('course_abbr', 20);

            // Teaching Scheme
            $table->unsignedSmallInteger('th_hours')->default(0);
            $table->unsignedSmallInteger('tu_hours')->default(0);
            $table->unsignedSmallInteger('pr_hours')->default(0);
            $table->unsignedSmallInteger('total_hours')->default(0);   // auto: th+tu+pr
            $table->decimal('credits', 5, 2)->default(0);

            // Examination Scheme
            $table->unsignedSmallInteger('theory_paper_hrs')->default(0);
            $table->unsignedSmallInteger('theory_max_marks')->default(0);
            $table->unsignedSmallInteger('test_max_marks')->default(0);
            $table->unsignedSmallInteger('pr_max_marks')->default(0);
            $table->unsignedSmallInteger('or_max_marks')->default(0);
            $table->unsignedSmallInteger('tw_max_marks')->default(0);
            $table->unsignedSmallInteger('total_marks')->default(0);   // auto sum of marks cols

            // Classification
            $table->enum('course_type', ['compulsory', 'elective', 'audit'])->default('compulsory');
            $table->string('elective_group', 50)->nullable();           // e.g. "Elective I"
            $table->boolean('is_common_course')->default(false);

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['programme_id', 'course_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
