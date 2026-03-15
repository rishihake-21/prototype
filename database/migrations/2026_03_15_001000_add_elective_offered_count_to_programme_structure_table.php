<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programme_structure', function (Blueprint $table) {
            $table->unsignedInteger('elective_offered_count')->default(0)->after('compulsory_count');
        });

        DB::table('programme_structure')->update([
            'elective_offered_count' => DB::raw('elective_count'),
        ]);
    }

    public function down(): void
    {
        Schema::table('programme_structure', function (Blueprint $table) {
            $table->dropColumn('elective_offered_count');
        });
    }
};
