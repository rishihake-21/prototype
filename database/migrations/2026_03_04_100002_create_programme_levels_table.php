<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programme_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->string('level_code', 20);    // e.g. Level-0, Level-1, ...Level-5
            $table->string('level_name');         // e.g. Audit Courses, Foundation Courses
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['programme_id', 'level_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programme_levels');
    }
};
