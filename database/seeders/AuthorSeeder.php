<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;

/**
 * AuthorSeeder — mengisi tabel authors dengan data contoh awal.
 */
class AuthorSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk mengisi data author.
     */
    public function run(): void
    {
        // Data author awal sebagai contoh
        $authors = [
            [
                'name'    => 'Pramoedya Ananta Toer',
                'bio'     => 'Sastrawan besar Indonesia, penulis Tetralogi Buru yang terkenal di seluruh dunia.',
                'country' => 'Indonesia',
            ],
            [
                'name'    => 'Andrea Hirata',
                'bio'     => 'Penulis novel Laskar Pelangi yang menjadi salah satu novel terlaris Indonesia.',
                'country' => 'Indonesia',
            ],
            [
                'name'    => 'J.K. Rowling',
                'bio'     => 'Penulis seri Harry Potter, salah satu penulis terlaris sepanjang masa di dunia.',
                'country' => 'United Kingdom',
            ],
            [
                'name'    => 'Stephen King',
                'bio'     => 'Raja horor modern, telah menulis lebih dari 60 novel dan 200 cerita pendek.',
                'country' => 'United States',
            ],
            [
                'name'    => 'Tere Liye',
                'bio'     => 'Penulis Indonesia produktif dengan lebih dari 40 novel yang telah diterbitkan.',
                'country' => 'Indonesia',
            ],
        ];

        // Menggunakan firstOrCreate agar tidak duplikat jika seeder dijalankan ulang
        foreach ($authors as $author) {
            Author::firstOrCreate(
                ['name' => $author['name']],
                [
                    'bio'     => $author['bio'],
                    'country' => $author['country'],
                ]
            );
        }
    }
}
