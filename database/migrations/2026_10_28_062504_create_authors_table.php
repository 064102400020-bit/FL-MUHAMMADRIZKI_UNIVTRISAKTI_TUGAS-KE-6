<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration untuk membuat tabel authors
return new class extends Migration
{
    /**
     * Jalankan migration (buat tabel authors).
     */
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();                                // Primary key auto increment
            $table->string('name');                      // Nama penulis, wajib diisi
            $table->text('bio')->nullable();             // Biografi penulis, opsional
            $table->string('country')->nullable();       // Negara asal penulis, opsional
            $table->timestamps();                        // created_at & updated_at
        });
    }

    /**
     * Batalkan migration (hapus tabel authors).
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
