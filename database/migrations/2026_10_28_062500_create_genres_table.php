<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration untuk membuat tabel genres
return new class extends Migration
{
    /**
     * Jalankan migration (buat tabel genres).
     */
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();                                      // Primary key auto increment
            $table->string('name')->unique();                  // Nama genre, wajib & unik
            $table->text('description')->nullable();           // Deskripsi genre, opsional
            $table->timestamps();                              // created_at & updated_at
        });
    }

    /**
     * Batalkan migration (hapus tabel genres).
     */
    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
