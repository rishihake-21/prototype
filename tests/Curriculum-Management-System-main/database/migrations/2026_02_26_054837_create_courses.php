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
        Schema::create('courses', function (Blueprint $table) {
        $table->id();

        $table->foreignId('scheme_id')->constrained()->onDelete('cascade');
        $table->foreignId('scheme_level_id')
            ->constrained('scheme_levels')
            ->onDelete('cascade');

        $table->string('course_code');
        $table->string('course_title');
        $table->string('Abbr')->nullable();
        $table->integer('year')->nullable();
        $table->enum('term', ['odd','even'])->nullable();
        $table->integer('th');
        $table->integer('tu')->nullable();
        $table->integer('pr');
        $table->integer('total_hours');
        $table->integer('credits');
        $table->integer('theory_hours')->default(0);
        $table->integer('theory_marks')->default(0);
        $table->integer('test_marks')->default(0);
        $table->integer('pr_marks')->default(0);
        $table->integer('or_marks')->default(0);
        $table->integer('tw_marks')->default(0);
        $table->integer('marks');
        $table->enum('type', ['compulsory', 'elective']);
        $table->boolean('is_audit')->default(0);
        $table->boolean('is_award')->default(1);
        $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
