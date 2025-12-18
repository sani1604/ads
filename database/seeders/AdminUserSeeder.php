<?php
// database/seeders/AdminUserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@agencyportal.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '9999999999',
            'is_active' => true,
            'is_onboarded' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Manager User',
            'email' => 'manager@agencyportal.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'phone' => '8888888888',
            'is_active' => true,
            'is_onboarded' => true,
            'email_verified_at' => now(),
        ]);
    }
}