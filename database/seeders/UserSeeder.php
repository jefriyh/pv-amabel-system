<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@amabel.id'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPERADMIN,
                'phone' => '081234567890',
                'annual_leave_quota' => 12,
                'is_active' => true,
            ]
        );

        // 2. Pengurus Komplek
        User::firstOrCreate(
            ['email' => 'pengurus@amabel.id'],
            [
                'name' => 'Pengurus RT/RW',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PENGURUS,
                'phone' => '081234567891',
                'annual_leave_quota' => 12,
                'is_active' => true,
            ]
        );

        // 3. Security
        User::firstOrCreate(
            ['email' => 'security@amabel.id'],
            [
                'name' => 'Danru Security Budi',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SECURITY,
                'phone' => '081234567892',
                'annual_leave_quota' => 12,
                'is_active' => true,
            ]
        );
    }
}
