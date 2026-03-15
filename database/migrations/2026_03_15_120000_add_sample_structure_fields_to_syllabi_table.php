<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->text('industry_employer_outcome')->nullable()->after('rationale');
            $table->text('self_learning')->nullable()->after('equipment_list');
            $table->json('special_instructional_strategies')->nullable()->after('self_learning');
        });
    }

    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropColumn([
                'industry_employer_outcome',
                'self_learning',
                'special_instructional_strategies',
            ]);
        });
    }
};
