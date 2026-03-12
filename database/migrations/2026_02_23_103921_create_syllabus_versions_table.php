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
        Schema::create('syllabus_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained('syllabi')->cascadeOnDelete();
            $table->tinyInteger('version_number');
            $table->longText('snapshot');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_versions');
    }
};
