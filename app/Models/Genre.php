<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Genre — merepresentasikan tabel 'genres' di database.
 * Menggunakan Eloquent ORM untuk interaksi dengan database.
 */
class Genre extends Model
{
    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     * Mencegah mass assignment vulnerability.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];
}
