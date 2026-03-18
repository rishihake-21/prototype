<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programme_levels', function (Blueprint $table) {
            $table->decimal('credits_limit', 6, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('programme_levels', function (Blueprint $table) {
            $table->unsignedSmallInteger('credits_limit')->default(0)->change();
        });
    }
};
