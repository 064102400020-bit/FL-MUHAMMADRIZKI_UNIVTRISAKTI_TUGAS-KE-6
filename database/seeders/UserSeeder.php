<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder akun pengguna (Admin dan Customer).
     */
    public function run(): void
    {
        // 1. Akun Admin untuk pengujian hak akses admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin BookSales',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // 2. Akun Customer 1 untuk pengujian transaksi
        User::updateOrCreate(
            ['email' => 'customer1@example.com'],
            [
                'name'     => 'Customer Satu',
                'password' => Hash::make('password'),
                'role'     => 'customer',
            ]
        );

        // 3. Akun Customer 2 untuk pengujian pembatasan antar-customer (403 Forbidden)
        User::updateOrCreate(
            ['email' => 'customer2@example.com'],
            [
                'name'     => 'Customer Dua',
                'password' => Hash::make('password'),
                'role'     => 'customer',
            ]
        );
    }
}
