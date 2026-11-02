<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — entry point utama untuk semua seeder.
 * Dipanggil dengan perintah: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan semua seeder yang diperlukan.
     */
    public function run(): void
    {
        // Jalankan seeder genre, author, buku, dan user akun testing
        $this->call([
            GenreSeeder::class,
            AuthorSeeder::class,
            BookSeeder::class,
            UserSeeder::class,
        ]);
    }
}
