<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * GenreController — mengelola endpoint API untuk data genre.
 * Mendukung API Resource:
 * - GET    /api/genres      (index)
 * - POST   /api/genres      (store)
 * - GET    /api/genres/{id} (show)
 * - PUT    /api/genres/{id} (update)
 * - PATCH  /api/genres/{id} (update)
 * - DELETE /api/genres/{id} (destroy)
 */
class GenreController extends Controller
{
    /**
     * GET /api/genres
     * Mengambil semua data genre dari database menggunakan Eloquent.
     *
     * @return JsonResponse HTTP 200 dengan daftar genre
     */
    public function index(): JsonResponse
    {
        // Ambil semua data genre menggunakan Eloquent ORM
        $genres = Genre::all();

        return response()->json([
            'success' => true,
            'message' => 'Data genre berhasil diambil',
            'data'    => $genres,
        ], 200); // HTTP 200 OK
    }

    /**
     * POST /api/genres
     * Membuat genre baru setelah validasi input.
     *
     * @param  Request $request  Data JSON dari body request
     * @return JsonResponse HTTP 201 dengan data genre yang baru dibuat
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi input yang dikirim client
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:genres,name', // Wajib, unik
            'description' => 'nullable|string',                            // Opsional
        ]);

        // Simpan genre baru ke database menggunakan mass assignment
        $genre = Genre::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data genre berhasil dibuat',
            'data'    => $genre,
        ], 201); // HTTP 201 Created
    }

    /**
     * GET /api/genres/{id}
     * Menampilkan detail data genre berdasarkan ID.
     *
     * @param  int|string $id ID genre yang dicari
     * @return JsonResponse HTTP 200 jika ditemukan, HTTP 404 jika tidak ditemukan
     */
    public function show($id): JsonResponse
    {
        // Cari data genre berdasarkan ID menggunakan Eloquent ORM
        $genre = Genre::find($id);

        // Jika data tidak ditemukan, kembalikan response 404
        if (! $genre) {
            return response()->json([
                'success' => false,
                'message' => 'Data Genre tidak ditemukan',
            ], 404); // HTTP 404 Not Found
        }

        // Kembalikan response sukses beserta detail data genre
        return response()->json([
            'success' => true,
            'message' => 'Data Genre berhasil ditemukan',
            'data'    => $genre,
        ], 200); // HTTP 200 OK
    }

    /**
     * PUT/PATCH /api/genres/{id}
     * Mengubah data genre berdasarkan ID setelah validasi input.
     *
     * @param  Request    $request Data JSON perubahan
     * @param  int|string $id      ID genre yang ingin diperbarui
     * @return JsonResponse HTTP 200 jika berhasil, HTTP 404 jika tidak ditemukan, HTTP 422 jika validasi gagal
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Cari data genre berdasarkan ID menggunakan Eloquent ORM
        $genre = Genre::find($id);

        // Jika data tidak ditemukan, kembalikan response 404
        if (! $genre) {
            return response()->json([
                'success' => false,
                'message' => 'Data Genre tidak ditemukan',
            ], 404); // HTTP 404 Not Found
        }

        // Validasi input pembaruan (nama unik kecuali untuk record ini sendiri)
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255|unique:genres,name,' . $id,
            'description' => 'nullable|string',
        ]);

        // Perbarui data genre dengan data tervalidasi
        $genre->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Genre berhasil diperbarui',
            'data'    => $genre,
        ], 200); // HTTP 200 OK
    }

    /**
     * DELETE /api/genres/{id}
     * Menghapus data genre berdasarkan ID.
     *
     * @param  int|string $id ID genre yang ingin dihapus
     * @return JsonResponse HTTP 200 jika berhasil dihapus, HTTP 404 jika tidak ditemukan
     */
    public function destroy($id): JsonResponse
    {
        // Cari data genre berdasarkan ID menggunakan Eloquent ORM
        $genre = Genre::find($id);

        // Jika data tidak ditemukan, kembalikan response 404
        if (! $genre) {
            return response()->json([
                'success' => false,
                'message' => 'Data Genre tidak ditemukan',
            ], 404); // HTTP 404 Not Found
        }

        // Hapus data dari database
        $genre->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Genre berhasil dihapus',
        ], 200); // HTTP 200 OK
    }
}
