<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CleanDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Departments
        $deptIf = Department::updateOrCreate(['code' => 'IF'], ['name' => 'Information Technology', 'is_active' => true]);
        $deptCm = Department::updateOrCreate(['code' => 'CM'], ['name' => 'Computer Engineering', 'is_active' => true]);

        // 2. Create Admin
        User::updateOrCreate(['email' => 'admin@demo.com'], [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // 3. Create CDC In-charge
        $cdc = User::updateOrCreate(['email' => 'cdc@demo.com'], [
            'name' => 'CDC Manager',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CDC,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // 4. Create HODs
        $hodIf = User::updateOrCreate(['email' => 'hod.if@demo.com'], [
            'name' => 'HOD IT',
            'password' => Hash::make('password'),
            'role' => User::ROLE_HOD,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $deptIf->update(['head_user_id' => $hodIf->id]);
        $hodIf->departments()->syncWithoutDetaching([$deptIf->id]);

        $hodCm = User::updateOrCreate(['email' => 'hod.cm@demo.com'], [
            'name' => 'HOD Computer',
            'password' => Hash::make('password'),
            'role' => User::ROLE_HOD,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $deptCm->update(['head_user_id' => $hodCm->id]);
        $hodCm->departments()->syncWithoutDetaching([$deptCm->id]);

        // 5. Create Faculty
        $facIf = User::updateOrCreate(['email' => 'fac.if@demo.com'], [
            'name' => 'Prof. Faculty IT',
            'password' => Hash::make('password'),
            'role' => User::ROLE_FACULTY,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $facIf->departments()->syncWithoutDetaching([$deptIf->id]);

        $facCm = User::updateOrCreate(['email' => 'fac.cm@demo.com'], [
            'name' => 'Prof. Faculty Computer',
            'password' => Hash::make('password'),
            'role' => User::ROLE_FACULTY,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $facCm->departments()->syncWithoutDetaching([$deptCm->id]);

        // 5.5 Create Observer
        User::updateOrCreate(['email' => 'observer@demo.com'], [
            'name' => 'Campus Observer',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OBSERVER,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->call(MsbteKSchemeSeeder::class);
    }
}
