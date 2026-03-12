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
        Schema::table('syllabus_reviews', function (Blueprint $table) {
            // Change the status enum to include more options
            $table->enum('status', ['approved', 'rejected', 'changes_requested'])->default('approved')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabus_reviews', function (Blueprint $table) {
            // Revert back to original enum
            $table->enum('status', ['approved', 'rejected'])->default('approved')->change();
        });
    }
};
