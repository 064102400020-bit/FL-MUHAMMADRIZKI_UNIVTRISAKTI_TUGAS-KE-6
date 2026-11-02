<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Jalankan seeder data buku awal.
     */
    public function run(): void
    {
        $authors = Author::all();
        $genres = Genre::all();

        $author1 = $authors->first();
        $author2 = $authors->count() > 1 ? $authors->get(1) : $author1;
        $genre1 = $genres->first();
        $genre2 = $genres->count() > 1 ? $genres->get(1) : $genre1;

        if ($author1) {
            Book::firstOrCreate(
                ['isbn' => '978-979-973-123-1'],
                [
                    'author_id'   => $author1->id,
                    'genre_id'    => $genre1 ? $genre1->id : null,
                    'title'       => 'Bumi Manusia',
                    'description' => 'Kisah Minke di era kolonial Hindia Belanda.',
                    'price'       => 95000,
                    'stock'       => 20,
                    'year'        => 1980,
                ]
            );

            Book::firstOrCreate(
                ['isbn' => '978-979-973-123-2'],
                [
                    'author_id'   => $author1->id,
                    'genre_id'    => $genre1 ? $genre1->id : null,
                    'title'       => 'Anak Semua Bangsa',
                    'description' => 'Buku kedua Tetralogi Buru.',
                    'price'       => 105000,
                    'stock'       => 15,
                    'year'        => 1981,
                ]
            );
        }

        if ($author2) {
            Book::firstOrCreate(
                ['isbn' => '978-979-306-279-2'],
                [
                    'author_id'   => $author2->id,
                    'genre_id'    => $genre2 ? $genre2->id : null,
                    'title'       => 'Laskar Pelangi',
                    'description' => 'Kisah perjuangan 10 anak di Belitung.',
                    'price'       => 85000,
                    'stock'       => 30,
                    'year'        => 2005,
                ]
            );
        }
    }
}
