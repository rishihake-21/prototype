<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@institution.edu'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@institution.edu',
                'password' => Hash::make('Admin@123456'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Email: admin@institution.edu');
        $this->command->info('Password: Admin@123456');
    }
}
