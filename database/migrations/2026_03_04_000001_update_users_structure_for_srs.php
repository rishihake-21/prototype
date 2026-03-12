<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aligns users with SRS: role enum (admin, cdc, hod, faculty, observer),
     * user_departments many-to-many (faculty in multiple departments).
     */
    public function up(): void
    {
        // 1. Create user_departments pivot (many-to-many) if not exists
        if (!Schema::hasTable('user_departments')) {
            Schema::create('user_departments', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('department_id');
                $table->primary(['user_id', 'department_id']);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            });
        }

        // 2. Migrate existing user department_id into user_departments (if column still exists)
        if (Schema::hasColumn('users', 'department_id')) {
            $usersWithDept = DB::table('users')->whereNotNull('department_id')->get(['id', 'department_id']);
            foreach ($usersWithDept as $u) {
                DB::table('user_departments')->insertOrIgnore([
                    'user_id' => $u->id,
                    'department_id' => $u->department_id,
                ]);
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        // 4. Add role column if not exists
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->nullable()->after('email');
            });
        }

        // 5. Migrate role from roles table (if role_id still exists)
        if (Schema::hasColumn('users', 'role_id')) {
            $map = [
                'admin' => 'admin',
                'creator' => 'faculty',
                'approver' => 'hod',
            ];
            $roleRows = DB::table('users')->whereNotNull('role_id')->get(['id', 'role_id']);
            foreach ($roleRows as $u) {
                $roleName = DB::table('roles')->where('id', $u->role_id)->value('name');
                $newRole = $map[$roleName] ?? 'faculty';
                DB::table('users')->where('id', $u->id)->update(['role' => $newRole]);
            }
        }

        DB::table('users')->whereNull('role')->update(['role' => 'faculty']);

        // 6. Drop role_id if still exists and enforce role NOT NULL
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }

        if (Schema::hasColumn('users', 'role') && DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'faculty'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore role_id column
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('email');
        });

        // Restore roles table usage: map role back to role_id (admin->admin, faculty->creator, hod->approver)
        $roles = DB::table('roles')->pluck('id', 'name');
        $adminId = $roles['admin'] ?? null;
        $creatorId = $roles['creator'] ?? null;
        $approverId = $roles['approver'] ?? null;

        if ($adminId) {
            DB::table('users')->where('role', 'admin')->update(['role_id' => $adminId]);
        }
        if ($creatorId) {
            DB::table('users')->whereIn('role', ['faculty', 'cdc', 'observer'])->update(['role_id' => $creatorId]);
        }
        if ($approverId) {
            DB::table('users')->where('role', 'hod')->update(['role_id' => $approverId]);
        }
        DB::table('users')->whereNull('role_id')->update(['role_id' => $creatorId ?? $approverId]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            $table->dropColumn('role');
        });

        // Restore department_id
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('role_id');
        });

        $pivot = DB::table('user_departments')->get();
        foreach ($pivot as $row) {
            DB::table('users')->where('id', $row->user_id)->update(['department_id' => $row->department_id]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        Schema::dropIfExists('user_departments');
    }
};
