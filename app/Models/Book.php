<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'genre_id',
        'title',
        'isbn',
        'description',
        'price',
        'stock',
        'year',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'year'  => 'integer',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    /**
     * Relasi kebalikannya: satu Book dapat memiliki banyak Transaksi.
     */
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
}
