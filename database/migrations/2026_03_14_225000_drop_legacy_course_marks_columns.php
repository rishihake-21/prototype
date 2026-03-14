<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'theory_max_marks',
                'test_max_marks',
                'pr_max_marks',
                'or_max_marks',
                'tw_max_marks',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedSmallInteger('theory_max_marks')->default(0);
            $table->unsignedSmallInteger('test_max_marks')->default(0);
            $table->unsignedSmallInteger('pr_max_marks')->default(0);
            $table->unsignedSmallInteger('or_max_marks')->default(0);
            $table->unsignedSmallInteger('tw_max_marks')->default(0);
        });
    }
};

