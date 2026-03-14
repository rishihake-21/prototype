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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('course_code', 20)->nullable()->change();
            $table->string('course_title', 255)->nullable()->change();
            $table->string('course_abbr', 20)->nullable()->change();
            
            // Note: In MySQL, multiple NULL values are allowed even in a unique index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('course_code', 20)->nullable(false)->change();
            $table->string('course_title', 255)->nullable(false)->change();
            $table->string('course_abbr', 20)->nullable(false)->change();
        });
    }
};
