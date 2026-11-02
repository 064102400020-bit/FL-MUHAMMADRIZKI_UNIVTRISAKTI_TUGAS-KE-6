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
        $author = Author::first();
        $genre = Genre::first();

        if ($author) {
            Book::firstOrCreate(
                ['isbn' => '978-979-973-123-1'],
                [
                    'author_id'   => $author->id,
                    'genre_id'    => $genre ? $genre->id : null,
                    'title'       => 'Bumi Manusia',
                    'description' => 'Kisah Minke di era kolonial Hindia Belanda.',
                    'price'       => 95000,
                    'stock'       => 20,
                    'year'        => 1980,
                ]
            );
        }
    }
}

