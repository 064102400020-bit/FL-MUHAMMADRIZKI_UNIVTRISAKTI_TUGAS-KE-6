<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Genre;

/**
 * GenreSeeder — mengisi tabel genres dengan data contoh awal.
 */
class GenreSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk mengisi data genre.
     */
    public function run(): void
    {
        // Data genre awal sebagai contoh
        $genres = [
            [
                'name'        => 'Fiksi',
                'description' => 'Karya sastra berupa cerita rekaan atau khayalan, tidak berdasarkan fakta nyata.',
            ],
            [
                'name'        => 'Non-Fiksi',
                'description' => 'Karya tulis berdasarkan fakta, data, dan kejadian nyata.',
            ],
            [
                'name'        => 'Sains Fiksi',
                'description' => 'Genre fiksi yang mengeksplorasi sains, teknologi masa depan, dan alam semesta.',
            ],
            [
                'name'        => 'Horor',
                'description' => 'Genre yang bertujuan menakut-nakuti pembaca dengan unsur supranatural atau psikologis.',
            ],
            [
                'name'        => 'Romansa',
                'description' => 'Genre yang berfokus pada kisah percintaan dan hubungan emosional antar karakter.',
            ],
        ];

        // Menggunakan firstOrCreate agar tidak duplikat jika seeder dijalankan ulang
        foreach ($genres as $genre) {
            Genre::firstOrCreate(
                ['name' => $genre['name']],
                ['description' => $genre['description']]
            );
        }
    }
}
