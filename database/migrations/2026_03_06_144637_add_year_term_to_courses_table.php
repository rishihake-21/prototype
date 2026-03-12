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
            $table->unsignedTinyInteger('year')->nullable()->after('course_abbr');
            $table->enum('term', ['odd', 'even'])->nullable()->after('year');
            $table->boolean('is_award')->default(true)->after('course_type');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['year', 'term', 'is_award']);
        });
    }
};
