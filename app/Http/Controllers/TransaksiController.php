<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransaksiController extends Controller
{
    /**
     * GET /api/transaksi
     * Menampilkan semua data transaksi beserta relasi user dan book (Khusus Admin).
     */
    public function index(): JsonResponse
    {
        // Eager loading relasi user dan book agar tidak terjadi masalah N+1 query
        $transaksis = Transaksi::with(['user', 'book'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Semua data transaksi berhasil diambil',
            'data'    => $transaksis,
        ], 200);
    }

    /**
     * POST /api/transaksi
     * Membuat transaksi baru (Khusus Customer).
     * user_id diambil otomatis dari user yang sedang login, total_harga dihitung di backend.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'jumlah'  => 'required|integer|min:1',
            'status'  => 'nullable|string|in:pending,paid,cancelled',
        ]);

        // Ambil data buku untuk menghitung total_harga berdasarkan harga asli di database
        $book = Book::findOrFail($validated['book_id']);

        // Hitung total harga server-side (harga buku × jumlah beli)
        $totalHarga = $book->price * $validated['jumlah'];

        // Simpan transaksi baru
        $transaksi = Transaksi::create([
            'user_id'     => $request->user()->id,
            'book_id'     => $book->id,
            'jumlah'      => $validated['jumlah'],
            'total_harga' => $totalHarga,
            'status'      => $validated['status'] ?? 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat',
            'data'    => $transaksi->load(['user', 'book']),
        ], 201);
    }

    /**
     * GET /api/transaksi/{id}
     * Menampilkan detail satu transaksi beserta relasi user dan book (Khusus Customer).
     * Customer hanya boleh melihat transaksi miliknya sendiri.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $transaksi = Transaksi::with(['user', 'book'])->find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Data Transaksi tidak ditemukan',
            ], 404);
        }

        // Validasi hak kepemilikan: Customer hanya boleh melihat transaksinya sendiri
        if ($request->user()->role === 'customer' && $transaksi->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk melihat transaksi ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil diambil',
            'data'    => $transaksi,
        ], 200);
    }

    /**
     * PUT/PATCH /api/transaksi/{id}
     * Mengubah data transaksi (Khusus Customer).
     * Customer hanya boleh mengubah transaksi miliknya sendiri.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Data Transaksi tidak ditemukan',
            ], 404);
        }

        // Validasi hak kepemilikan: Customer hanya boleh mengubah transaksinya sendiri
        if ($request->user()->role === 'customer' && $transaksi->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengubah transaksi ini.',
            ], 403);
        }

        $validated = $request->validate([
            'book_id' => 'sometimes|required|exists:books,id',
            'jumlah'  => 'sometimes|required|integer|min:1',
            'status'  => 'nullable|string|in:pending,paid,cancelled',
        ]);

        $bookId = $validated['book_id'] ?? $transaksi->book_id;
        $jumlah = $validated['jumlah'] ?? $transaksi->jumlah;
        $book = Book::findOrFail($bookId);

        // Hitung ulang total_harga jika ada perubahan buku atau jumlah
        $totalHarga = $book->price * $jumlah;

        $transaksi->update([
            'book_id'     => $bookId,
            'jumlah'      => $jumlah,
            'total_harga' => $totalHarga,
            'status'      => $validated['status'] ?? $transaksi->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diperbarui',
            'data'    => $transaksi->fresh()->load(['user', 'book']),
        ], 200);
    }

    /**
     * DELETE /api/transaksi/{id}
     * Menghapus transaksi (Khusus Admin).
     */
    public function destroy(string $id): JsonResponse
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Data Transaksi tidak ditemukan',
            ], 404);
        }

        $transaksi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus',
        ], 200);
    }
}
