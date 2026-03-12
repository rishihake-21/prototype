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
        Schema::table('programme_levels', function (Blueprint $table) {
            $table->unsignedSmallInteger('courses_limit')->default(0)->after('sort_order');
            $table->unsignedSmallInteger('th_limit')->default(0)->after('courses_limit');
            $table->unsignedSmallInteger('tu_limit')->default(0)->after('th_limit');
            $table->unsignedSmallInteger('pr_limit')->default(0)->after('tu_limit');
            $table->unsignedSmallInteger('hours_limit')->default(0)->after('pr_limit');
            $table->unsignedSmallInteger('credits_limit')->default(0)->after('hours_limit');
            $table->unsignedInteger('marks_limit')->default(0)->after('credits_limit');
            $table->boolean('is_audit')->default(false)->after('marks_limit');
        });
    }

    public function down(): void
    {
        Schema::table('programme_levels', function (Blueprint $table) {
            $table->dropColumn([
                'courses_limit', 'th_limit', 'tu_limit', 'pr_limit', 
                'hours_limit', 'credits_limit', 'marks_limit', 'is_audit'
            ]);
        });
    }
};
