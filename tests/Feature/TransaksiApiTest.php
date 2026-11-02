<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransaksiApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer1;
    protected User $customer2;
    protected Book $book;
    protected Transaksi $transaksiCustomer1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Akun Admin
        $this->admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        // 2. Akun Customer 1
        $this->customer1 = User::create([
            'name'     => 'Customer Satu',
            'email'    => 'customer1@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        // 3. Akun Customer 2
        $this->customer2 = User::create([
            'name'     => 'Customer Dua',
            'email'    => 'customer2@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        // 4. Data Master Buku
        $author = Author::create([
            'name'    => 'Pramoedya Ananta Toer',
            'country' => 'Indonesia',
        ]);

        $genre = Genre::create([
            'name'        => 'Sejarah',
            'description' => 'Fiksi Sejarah',
        ]);

        $this->book = Book::create([
            'author_id'   => $author->id,
            'genre_id'    => $genre->id,
            'title'       => 'Bumi Manusia',
            'isbn'        => '978-979-973-123-1',
            'description' => 'Novel Tetralogi Buru',
            'price'       => 100000,
            'stock'       => 20,
            'year'        => 1980,
        ]);

        // 5. Transaksi milik Customer 1
        $this->transaksiCustomer1 = Transaksi::create([
            'user_id'     => $this->customer1->id,
            'book_id'     => $this->book->id,
            'jumlah'      => 2,
            'total_harga' => 200000,
            'status'      => 'pending',
        ]);
    }

    // =========================================================================
    // 1. PENGUJIAN TANPA TOKEN (Expect: 401 Unauthorized)
    // =========================================================================

    public function test_unauthenticated_cannot_access_transaksi_endpoints(): void
    {
        // GET all
        $this->getJson('/api/transaksi')->assertStatus(401);

        // POST create
        $this->postJson('/api/transaksi', [
            'book_id' => $this->book->id,
            'jumlah'  => 1,
        ])->assertStatus(401);

        // GET detail
        $this->getJson("/api/transaksi/{$this->transaksiCustomer1->id}")->assertStatus(401);

        // PUT update
        $this->putJson("/api/transaksi/{$this->transaksiCustomer1->id}", [
            'jumlah' => 3,
        ])->assertStatus(401);

        // DELETE
        $this->deleteJson("/api/transaksi/{$this->transaksiCustomer1->id}")->assertStatus(401);
    }

    // =========================================================================
    // 2. PENGUJIAN HAK AKSES CUSTOMER (Create, Show, Update: Sukses; Index, Destroy: 403)
    // =========================================================================

    public function test_customer_can_create_transaksi_with_server_side_total_harga(): void
    {
        Sanctum::actingAs($this->customer1);

        $response = $this->postJson('/api/transaksi', [
            'book_id' => $this->book->id,
            'jumlah'  => 3, // 3 × 100000 = 300000
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
            ])
            ->assertJsonPath('data.user_id', $this->customer1->id)
            ->assertJsonPath('data.book_id', $this->book->id)
            ->assertJsonPath('data.jumlah', 3)
            ->assertJsonPath('data.total_harga', '300000.00')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'book_id',
                    'jumlah',
                    'total_harga',
                    'status',
                    'user' => ['id', 'name', 'email'],
                    'book' => ['id', 'title', 'price'],
                ],
            ]);

        $this->assertDatabaseHas('transaksis', [
            'user_id'     => $this->customer1->id,
            'book_id'     => $this->book->id,
            'jumlah'      => 3,
            'total_harga' => 300000,
        ]);
    }

    public function test_customer_can_view_own_transaksi_detail(): void
    {
        Sanctum::actingAs($this->customer1);

        $response = $this->getJson("/api/transaksi/{$this->transaksiCustomer1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'id'          => $this->transaksiCustomer1->id,
                    'user_id'     => $this->customer1->id,
                    'book_id'     => $this->book->id,
                    'total_harga' => '200000.00',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'book' => ['id', 'title'],
                ],
            ]);
    }

    public function test_customer_can_update_own_transaksi(): void
    {
        Sanctum::actingAs($this->customer1);

        $response = $this->putJson("/api/transaksi/{$this->transaksiCustomer1->id}", [
            'jumlah' => 4, // 4 × 100000 = 400000
            'status' => 'paid',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui',
            ])
            ->assertJsonPath('data.jumlah', 4)
            ->assertJsonPath('data.total_harga', '400000.00')
            ->assertJsonPath('data.status', 'paid');
    }

    public function test_customer_cannot_get_all_transaksi(): void
    {
        Sanctum::actingAs($this->customer1);

        $this->getJson('/api/transaksi')
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Endpoint ini hanya dapat diakses oleh admin.',
            ]);
    }

    public function test_customer_cannot_delete_transaksi(): void
    {
        Sanctum::actingAs($this->customer1);

        $this->deleteJson("/api/transaksi/{$this->transaksiCustomer1->id}")
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Endpoint ini hanya dapat diakses oleh admin.',
            ]);
    }

    // =========================================================================
    // 3. PENGUJIAN PEMBATASAN ANTAR CUSTOMER (Expect: 403 Forbidden)
    // =========================================================================

    public function test_customer_cannot_view_other_customer_transaksi(): void
    {
        // Customer 2 mencoba melihat transaksi milik Customer 1
        Sanctum::actingAs($this->customer2);

        $response = $this->getJson("/api/transaksi/{$this->transaksiCustomer1->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk melihat transaksi ini.',
            ]);
    }

    public function test_customer_cannot_update_other_customer_transaksi(): void
    {
        // Customer 2 mencoba mengubah transaksi milik Customer 1
        Sanctum::actingAs($this->customer2);

        $response = $this->putJson("/api/transaksi/{$this->transaksiCustomer1->id}", [
            'jumlah' => 5,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengubah transaksi ini.',
            ]);
    }

    // =========================================================================
    // 4. PENGUJIAN HAK AKSES ADMIN (Index & Destroy: Sukses; Customer Route: 403)
    // =========================================================================

    public function test_admin_can_get_all_transaksi_with_eager_loading(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/transaksi');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'book_id',
                        'jumlah',
                        'total_harga',
                        'status',
                        'user' => ['id', 'name', 'email'],
                        'book' => ['id', 'title', 'price'],
                    ],
                ],
            ]);
    }

    public function test_admin_can_delete_transaksi(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/transaksi/{$this->transaksiCustomer1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('transaksis', [
            'id' => $this->transaksiCustomer1->id,
        ]);
    }

    public function test_admin_cannot_access_customer_transaksi_routes(): void
    {
        Sanctum::actingAs($this->admin);

        // Admin mencoba mengakses route customer
        $this->postJson('/api/transaksi', [
            'book_id' => $this->book->id,
            'jumlah'  => 1,
        ])->assertStatus(403)->assertJson([
            'success' => false,
            'message' => 'Akses ditolak. Endpoint ini hanya dapat diakses oleh customer.',
        ]);

        $this->getJson("/api/transaksi/{$this->transaksiCustomer1->id}")
            ->assertStatus(403);

        $this->putJson("/api/transaksi/{$this->transaksiCustomer1->id}", [
            'jumlah' => 1,
        ])->assertStatus(403);
    }

    // =========================================================================
    // 5. PENGUJIAN VALIDASI INPUT (Expect: 422 Unprocessable Content)
    // =========================================================================

    public function test_create_transaksi_fails_when_book_does_not_exist(): void
    {
        Sanctum::actingAs($this->customer1);

        $response = $this->postJson('/api/transaksi', [
            'book_id' => 99999,
            'jumlah'  => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['book_id'],
            ]);
    }

    public function test_create_transaksi_fails_when_jumlah_is_zero_or_negative(): void
    {
        Sanctum::actingAs($this->customer1);

        $response = $this->postJson('/api/transaksi', [
            'book_id' => $this->book->id,
            'jumlah'  => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['jumlah'],
            ]);
    }
}
