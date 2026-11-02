<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit (mengikuti konvensi proyek).
     */
    protected $table = 'transaksis';

    /**
     * Kolom yang dapat diisi melalui mass assignment.
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'jumlah',
        'total_harga',
        'status',
    ];

    /**
     * Tipe casting untuk atribut model.
     */
    protected $casts = [
        'jumlah'      => 'integer',
        'total_harga' => 'decimal:2',
    ];

    /**
     * Relasi ke model User (pengguna/customer yang melakukan transaksi).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Book (buku yang dibeli pada transaksi ini).
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
