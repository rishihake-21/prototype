<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sample Path — recommended term-wise course distribution.
     */
    public function up(): void
    {
        Schema::create('sample_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->string('entry_level', 20)->default('10+'); // e.g. 10+, 12+, Lateral
            $table->unsignedTinyInteger('term_number');        // 1–6 (Odd/Even × Year)
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['programme_id', 'entry_level', 'course_id'], 'sample_path_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_paths');
    }
};
