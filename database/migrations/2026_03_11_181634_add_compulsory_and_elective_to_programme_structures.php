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
        Schema::table('programme_structure', function (Blueprint $table) {
            $table->unsignedInteger('compulsory_count')->default(0)->after('courses_to_complete');
            $table->unsignedInteger('elective_count')->default(0)->after('compulsory_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programme_structure', function (Blueprint $table) {
            $table->dropColumn(['compulsory_count', 'elective_count']);
        });
    }
};
