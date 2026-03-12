<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scheme at a Glance — one row per level per programme.
     */
    public function up(): void
    {
        Schema::create('programme_structure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('programme_levels')->cascadeOnDelete();
            $table->unsignedSmallInteger('total_courses_offered')->default(0);
            $table->unsignedSmallInteger('courses_to_complete')->default(0);
            $table->unsignedSmallInteger('th_hours')->default(0);
            $table->unsignedSmallInteger('tu_hours')->default(0);
            $table->unsignedSmallInteger('pr_hours')->default(0);
            $table->unsignedSmallInteger('total_hours')->default(0);  // th+tu+pr (stored for snapshot)
            $table->decimal('total_credits', 6, 2)->default(0);
            $table->unsignedSmallInteger('total_marks')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['programme_id', 'level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programme_structure');
    }
};
