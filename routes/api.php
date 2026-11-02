<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TransaksiController;

/*
|--------------------------------------------------------------------------
| API Routes — Tugas Framework Laravel ke-6: Middleware & Routing (Book Sales)
| Mahasiswa: MUHAMMAD RIZKI | Universitas Trisakti
|--------------------------------------------------------------------------
|
| Semua route di bawah ini secara otomatis memiliki prefix /api
| sebagaimana dikonfigurasi pada bootstrap/app.php.
|
*/

// =========================================================================
// 1. ROUTE PUBLIK (Dapat diakses siapa saja tanpa token)
// =========================================================================

// Endpoint Autentikasi Publik
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoint Publik: Read All (index) dan Detail (show) untuk Author, Genre, dan Book
Route::apiResource('authors', AuthorController::class)->only(['index', 'show']);
Route::apiResource('genres', GenreController::class)->only(['index', 'show']);
Route::apiResource('books', BookController::class)->only(['index', 'show']);


// =========================================================================
// 2. ROUTE TERPROTEKSI PENGGUNA TERAUTENTIKASI (Harus Login / Punya Bearer Token)
// =========================================================================

Route::middleware('auth:sanctum')->group(function () {
    // Endpoint Manajemen Sesi & Profil Pengguna
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});


// =========================================================================
// 3. ROUTE KHUSUS CUSTOMER (Harus Login + Role 'customer')
// =========================================================================

Route::middleware(['auth:sanctum', 'customer'])->group(function () {
    // Operasi Transaksi bagi Customer: Create (store), Update, dan Show Detail
    Route::apiResource('transaksi', TransaksiController::class)->only(['store', 'show', 'update']);
});


// =========================================================================
// 4. ROUTE KHUSUS ADMIN (Harus Login + Role 'admin')
// =========================================================================

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Operasi Create (store), Update, dan Destroy (delete) untuk Author, Genre, dan Book
    Route::apiResource('authors', AuthorController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('genres', GenreController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('books', BookController::class)->only(['store', 'update', 'destroy']);

    // Operasi Transaksi bagi Admin: Read All (index) dan Destroy (delete)
    Route::apiResource('transaksi', TransaksiController::class)->only(['index', 'destroy']);
});
