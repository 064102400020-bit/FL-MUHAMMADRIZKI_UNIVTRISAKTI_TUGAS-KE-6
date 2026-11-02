<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAndRoleApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Author $author;
    protected Genre $genre;
    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan akun admin dan user biasa
        $this->adminUser = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->regularUser = User::create([
            'name'     => 'Regular Test',
            'email'    => 'user@example.com',
            'password' => bcrypt('password'),
            'role'     => 'user',
        ]);

        // Siapkan data awal author, genre, dan book
        $this->author = Author::create([
            'name'    => 'Pramoedya Ananta Toer',
            'bio'     => 'Penulis Bumi Manusia',
            'country' => 'Indonesia',
        ]);

        $this->genre = Genre::create([
            'name'        => 'Historical Fiction',
            'description' => 'Fiksi sejarah masa lalu',
        ]);

        $this->book = Book::create([
            'author_id'   => $this->author->id,
            'genre_id'    => $this->genre->id,
            'title'       => 'Bumi Manusia',
            'isbn'        => '978-979-973-123-1',
            'description' => 'Kisah Minke',
            'price'       => 95000,
            'stock'       => 15,
            'year'        => 1980,
        ]);
    }

    // =========================================================================
    // 1. PENGUJIAN ENDPOINT AUTENTIKASI (Register, Login, Profile, Logout)
    // =========================================================================

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name'     => 'Mahasiswa Baru',
            'email'    => 'mahasiswa@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registrasi berhasil',
            ])
            ->assertJsonStructure([
                'data' => ['user', 'access_token', 'token_type'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'mahasiswa@example.com',
            'role'  => 'customer', // Default harus 'customer'
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil',
            ])
            ->assertJsonStructure([
                'data' => ['user', 'access_token', 'token_type'],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email atau kata sandi tidak valid',
            ]);
    }

    public function test_authenticated_user_can_access_profile_and_logout(): void
    {
        Sanctum::actingAs($this->regularUser);

        $profileResponse = $this->getJson('/api/profile');
        $profileResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['email' => 'user@example.com'],
            ]);

        $logoutResponse = $this->postJson('/api/logout');
        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout berhasil. Token telah dihapus.',
            ]);
    }

    // =========================================================================
    // 2. PENGUJIAN ROUTE PUBLIK TANPA TOKEN (Expect: 200 OK)
    // =========================================================================

    public function test_public_can_get_all_authors_without_token(): void
    {
        $response = $this->getJson('/api/authors');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_public_can_get_author_detail_without_token(): void
    {
        $response = $this->getJson("/api/authors/{$this->author->id}");
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_public_can_get_all_genres_without_token(): void
    {
        $response = $this->getJson('/api/genres');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_public_can_get_genre_detail_without_token(): void
    {
        $response = $this->getJson("/api/genres/{$this->genre->id}");
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_public_can_get_all_books_without_token(): void
    {
        $response = $this->getJson('/api/books');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_public_can_get_book_detail_without_token(): void
    {
        $response = $this->getJson("/api/books/{$this->book->id}");
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // =========================================================================
    // 3. PENGUJIAN AKSI ADMIN TANPA TOKEN (Expect: 401 Unauthorized)
    // =========================================================================

    public function test_unauthenticated_user_cannot_create_author(): void
    {
        $response = $this->postJson('/api/authors', ['name' => 'Penulis Baru']);
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_create_genre(): void
    {
        $response = $this->postJson('/api/genres', ['name' => 'Genre Baru']);
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_create_book(): void
    {
        $response = $this->postJson('/api/books', [
            'author_id' => $this->author->id,
            'title'     => 'Buku Baru',
        ]);
        $response->assertStatus(401);
    }

    // =========================================================================
    // 4. PENGUJIAN AKSI ADMIN DENGAN TOKEN USER BIASA (Expect: 403 Forbidden)
    // =========================================================================

    public function test_regular_user_cannot_create_author(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->postJson('/api/authors', ['name' => 'Penulis Ilegal']);
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Endpoint ini hanya dapat diakses oleh admin.',
            ]);
    }

    public function test_regular_user_cannot_update_genre(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->putJson("/api/genres/{$this->genre->id}", ['name' => 'Genre Update']);
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Endpoint ini hanya dapat diakses oleh admin.',
            ]);
    }

    public function test_regular_user_cannot_delete_book(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->deleteJson("/api/books/{$this->book->id}");
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Endpoint ini hanya dapat diakses oleh admin.',
            ]);
    }

    // =========================================================================
    // 5. PENGUJIAN AKSI DENGAN TOKEN ADMIN (Expect: 201 Created / 200 OK)
    // =========================================================================

    public function test_admin_can_create_author(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/authors', [
            'name'    => 'Tere Liye',
            'bio'     => 'Penulis Buku Serial Bumi',
            'country' => 'Indonesia',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data author berhasil dibuat',
            ]);

        $this->assertDatabaseHas('authors', ['name' => 'Tere Liye']);
    }

    public function test_admin_can_create_genre(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/genres', [
            'name'        => 'Fantasi',
            'description' => 'Dunia magis dan petualangan imajinatif',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data genre berhasil dibuat',
            ]);

        $this->assertDatabaseHas('genres', ['name' => 'Fantasi']);
    }

    public function test_admin_can_create_book(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/books', [
            'author_id'   => $this->author->id,
            'genre_id'    => $this->genre->id,
            'title'       => 'Jejak Langkah',
            'isbn'        => '978-979-973-123-2',
            'description' => 'Buku ketiga Tetralogi Buru',
            'price'       => 105000,
            'stock'       => 10,
            'year'        => 1985,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Book berhasil ditambahkan',
            ]);

        $this->assertDatabaseHas('books', ['title' => 'Jejak Langkah']);
    }

    public function test_admin_can_update_book(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->putJson("/api/books/{$this->book->id}", [
            'title' => 'Bumi Manusia Edisi Spesial',
            'price' => 120000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Book berhasil diperbarui',
            ]);

        $this->assertDatabaseHas('books', ['title' => 'Bumi Manusia Edisi Spesial']);
    }

    public function test_admin_can_delete_book(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->deleteJson("/api/books/{$this->book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Book berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('books', ['id' => $this->book->id]);
    }
}

