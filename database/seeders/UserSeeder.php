<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder akun pengguna (Admin dan User Biasa).
     */
    public function run(): void
    {
        // 1. Akun Admin untuk pengujian hak akses penuh
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin BookSales',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // 2. Akun User Biasa untuk pengujian pembatasan hak akses (403 Forbidden)
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name'     => 'User Biasa',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ]
        );
    }
}

