<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    /**
     * GET /api/books
     * Menampilkan semua buku beserta data author dan genre
     */
    public function index(): JsonResponse
    {
        $books = Book::with(['author', 'genre'])->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data books berhasil diambil',
            'data'    => $books,
        ], 200);
    }

    /**
     * POST /api/books
     * Menambahkan buku baru
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'author_id'   => 'required|exists:authors,id',
            'genre_id'    => 'nullable|exists:genres,id',
            'title'       => 'required|string|max:255',
            'isbn'        => 'nullable|string|unique:books,isbn|max:20',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'year'        => 'nullable|integer|min:1000|max:9999',
        ]);

        $book = Book::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Book berhasil ditambahkan',
            'data'    => $book->load(['author', 'genre']),
        ], 201);
    }

    /**
     * GET /api/books/{id}
     * Menampilkan detail satu buku beserta author dan genre
     */
    public function show(string $id): JsonResponse
    {
        $book = Book::with(['author', 'genre'])->find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data book berhasil diambil',
            'data'    => $book,
        ], 200);
    }

    /**
     * PUT/PATCH /api/books/{id}
     * Memperbarui data buku
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'author_id'   => 'sometimes|required|exists:authors,id',
            'genre_id'    => 'nullable|exists:genres,id',
            'title'       => 'sometimes|required|string|max:255',
            'isbn'        => 'nullable|string|unique:books,isbn,' . $book->id . '|max:20',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'year'        => 'nullable|integer|min:1000|max:9999',
        ]);

        $book->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Book berhasil diperbarui',
            'data'    => $book->fresh()->load(['author', 'genre']),
        ], 200);
    }

    /**
     * DELETE /api/books/{id}
     * Menghapus buku
     */
    public function destroy(string $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book tidak ditemukan',
            ], 404);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book berhasil dihapus',
        ], 200);
    }
}
