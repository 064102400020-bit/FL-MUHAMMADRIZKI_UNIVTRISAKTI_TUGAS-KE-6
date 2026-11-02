<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Author — merepresentasikan tabel 'authors' di database.
 * Menggunakan Eloquent ORM untuk interaksi dengan database.
 */
class Author extends Model
{
    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     * Mencegah mass assignment vulnerability.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'bio',
        'country',
    ];
}
