<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained('roles')->nullOnDelete();
        });

        // Migrate existing role data
        DB::statement("
            UPDATE users SET role_id = (
                SELECT id FROM roles WHERE roles.name = users.role
            ) WHERE role IS NOT NULL
        ");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['creator', 'approver', 'admin'])->default('creator');
        });

        // Restore role data
        DB::statement("
            UPDATE users SET role = (
                SELECT name FROM roles WHERE roles.id = users.role_id
            ) WHERE role_id IS NOT NULL
        ");

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
