<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * AuthorController — mengelola endpoint API untuk data penulis (author).
 * Mendukung API Resource:
 * - GET    /api/authors      (index)
 * - POST   /api/authors      (store)
 * - GET    /api/authors/{id} (show)
 * - PUT    /api/authors/{id} (update)
 * - PATCH  /api/authors/{id} (update)
 * - DELETE /api/authors/{id} (destroy)
 */
class AuthorController extends Controller
{
    /**
     * GET /api/authors
     * Mengambil semua data author dari database menggunakan Eloquent.
     *
     * @return JsonResponse HTTP 200 dengan daftar author
     */
    public function index(): JsonResponse
    {
        // Ambil semua data author menggunakan Eloquent ORM
        $authors = Author::all();

        return response()->json([
            'success' => true,
            'message' => 'Data author berhasil diambil',
            'data'    => $authors,
        ], 200); // HTTP 200 OK
    }

    /**
     * POST /api/authors
     * Membuat author baru setelah validasi input.
     *
     * @param  Request $request Data JSON dari body request
     * @return JsonResponse HTTP 201 dengan data author yang baru dibuat
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi input yang dikirim client
        $validated = $request->validate([
            'name'    => 'required|string|max:255', // Wajib diisi
            'bio'     => 'nullable|string',         // Opsional
            'country' => 'nullable|string|max:100', // Opsional, maks 100 karakter
        ]);

        // Simpan author baru ke database menggunakan mass assignment
        $author = Author::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data author berhasil dibuat',
            'data'    => $author,
        ], 201); // HTTP 201 Created
    }

    /**
     * GET /api/authors/{id}
     * Menampilkan detail data author berdasarkan ID.
     *
     * @param  int|string $id ID author yang dicari
     * @return JsonResponse HTTP 200 jika ditemukan, HTTP 404 jika tidak ditemukan
     */
    public function show($id): JsonResponse
    {
        // Cari data author berdasarkan ID menggunakan Eloquent ORM
        $author = Author::find($id);

        // Jika data tidak ditemukan, kembalikan response 404
        if (! $author) {
            return response()->json([
                'success' => false,
                'message' => 'Data Author tidak ditemukan',
            ], 404); // HTTP 404 Not Found
        }

        // Kembalikan response sukses beserta detail data author
        return response()->json([
            'success' => true,
            'message' => 'Data Author berhasil ditemukan',
            'data'    => $author,
        ], 200); // HTTP 200 OK
    }

    /**
     * PUT/PATCH /api/authors/{id}
     * Mengubah data author berdasarkan ID setelah validasi input.
     *
     * @param  Request    $request Data JSON perubahan
     * @param  int|string $id      ID author yang ingin diperbarui
     * @return JsonResponse HTTP 200 jika berhasil, HTTP 404 jika tidak ditemukan, HTTP 422 jika validasi gagal
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Cari data author berdasarkan ID menggunakan Eloquent ORM
        $author = Author::find($id);

        // Jika data tidak ditemukan, kembalikan response 404
        if (! $author) {
            return response()->json([
                'success' => false,
                'message' => 'Data Author tidak ditemukan',
            ], 404); // HTTP 404 Not Found
        }

        // Validasi input pembaruan
        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'bio'     => 'nullable|string',
            'country' => 'nullable|string|max:100',
        ]);

        // Perbarui data author dengan data tervalidasi
        $author->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Author berhasil diperbarui',
            'data'    => $author,
        ], 200); // HTTP 200 OK
    }

    /**
     * DELETE /api/authors/{id}
     * Menghapus data author berdasarkan ID.
     *
     * @param  int|string $id ID author yang ingin dihapus
     * @return JsonResponse HTTP 200 jika berhasil dihapus, HTTP 404 jika tidak ditemukan
     */
    public function destroy($id): JsonResponse
    {
        // Cari data author berdasarkan ID menggunakan Eloquent ORM
        $author = Author::find($id);

        // Jika data tidak ditemukan, kembalikan response 404
        if (! $author) {
            return response()->json([
                'success' => false,
                'message' => 'Data Author tidak ditemukan',
            ], 404); // HTTP 404 Not Found
        }

        // Hapus data dari database
        $author->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Author berhasil dihapus',
        ], 200); // HTTP 200 OK
    }
}
