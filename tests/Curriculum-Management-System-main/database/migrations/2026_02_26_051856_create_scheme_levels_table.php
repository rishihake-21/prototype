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
        Schema::create('scheme_levels', function (Blueprint $table) {
        $table->id();
        $table->foreignId('scheme_id')->constrained()->onDelete('cascade');

        $table->string('level_name');
        $table->boolean('is_audit')->default(false);
        $table->integer('courses_offered')->default(0);
        $table->integer('th')->default(0);
        $table->integer('tu')->default(0);
        $table->integer('pr')->default(0);
        $table->integer('total_hours')->default(0);
        $table->integer('total_credits')->default(0);
        $table->integer('marks')->default(0);

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_levels');
    }
};
