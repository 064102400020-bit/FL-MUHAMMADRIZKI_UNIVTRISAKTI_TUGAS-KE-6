<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    /**
     * Jalankan seeder contoh data transaksi.
     */
    public function run(): void
    {
        $customer1 = User::where('email', 'customer1@example.com')->first();
        $customer2 = User::where('email', 'customer2@example.com')->first();
        $books = Book::all();

        if ($customer1 && $books->count() > 0) {
            $book1 = $books->first();
            $jumlah1 = 2;
            Transaksi::firstOrCreate(
                [
                    'user_id' => $customer1->id,
                    'book_id' => $book1->id,
                ],
                [
                    'jumlah'      => $jumlah1,
                    'total_harga' => $book1->price * $jumlah1,
                    'status'      => 'paid',
                ]
            );
        }

        if ($customer2 && $books->count() > 1) {
            $book2 = $books->get(1);
            $jumlah2 = 1;
            Transaksi::firstOrCreate(
                [
                    'user_id' => $customer2->id,
                    'book_id' => $book2->id,
                ],
                [
                    'jumlah'      => $jumlah2,
                    'total_harga' => $book2->price * $jumlah2,
                    'status'      => 'pending',
                ]
            );
        }
    }
}
