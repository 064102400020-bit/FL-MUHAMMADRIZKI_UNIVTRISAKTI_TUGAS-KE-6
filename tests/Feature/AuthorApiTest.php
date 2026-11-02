<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Admin Author Test',
            'email'    => 'admin@author.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);
    }

    /**
     * Test GET /api/authors (index)
     */
    public function test_can_get_all_authors(): void
    {
        Author::create([
            'name'    => 'Pramoedya Ananta Toer',
            'bio'     => 'Sastrawan Indonesia',
            'country' => 'Indonesia',
        ]);

        $response = $this->getJson('/api/authors');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data author berhasil diambil',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'bio', 'country', 'created_at', 'updated_at'],
                ],
            ]);
    }

    /**
     * Test POST /api/authors (store)
     */
    public function test_can_create_author(): void
    {
        Sanctum::actingAs($this->admin);

        $payload = [
            'name'    => 'Andrea Hirata',
            'bio'     => 'Penulis Laskar Pelangi',
            'country' => 'Indonesia',
        ];

        $response = $this->postJson('/api/authors', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data author berhasil dibuat',
                'data'    => [
                    'name'    => 'Andrea Hirata',
                    'bio'     => 'Penulis Laskar Pelangi',
                    'country' => 'Indonesia',
                ],
            ]);

        $this->assertDatabaseHas('authors', [
            'name' => 'Andrea Hirata',
        ]);
    }

    /**
     * Test GET /api/authors/{id} (show sukses)
     */
    public function test_can_show_author_by_id(): void
    {
        $author = Author::create([
            'name'    => 'J.K. Rowling',
            'bio'     => 'Penulis Harry Potter',
            'country' => 'United Kingdom',
        ]);

        $response = $this->getJson("/api/authors/{$author->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Author berhasil ditemukan',
                'data'    => [
                    'id'      => $author->id,
                    'name'    => 'J.K. Rowling',
                    'bio'     => 'Penulis Harry Potter',
                    'country' => 'United Kingdom',
                ],
            ]);
    }

    /**
     * Test GET /api/authors/{id} (show gagal / 404)
     */
    public function test_show_author_not_found(): void
    {
        $response = $this->getJson('/api/authors/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data Author tidak ditemukan',
            ]);
    }

    /**
     * Test PUT /api/authors/{id} (update sukses data valid)
     */
    public function test_can_update_author_with_valid_data(): void
    {
        Sanctum::actingAs($this->admin);

        $author = Author::create([
            'name'    => 'Tere Liye',
            'bio'     => 'Bio lama',
            'country' => 'Indonesia',
        ]);

        $payload = [
            'name'    => 'Tere Liye (Updated)',
            'bio'     => 'Penulis novel terkenal Indonesia',
            'country' => 'Indonesia',
        ];

        $response = $this->putJson("/api/authors/{$author->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Author berhasil diperbarui',
                'data'    => [
                    'id'      => $author->id,
                    'name'    => 'Tere Liye (Updated)',
                    'bio'     => 'Penulis novel terkenal Indonesia',
                    'country' => 'Indonesia',
                ],
            ]);

        $this->assertDatabaseHas('authors', [
            'id'   => $author->id,
            'name' => 'Tere Liye (Updated)',
        ]);
    }

    /**
     * Test PATCH /api/authors/{id} (update sebagian data)
     */
    public function test_can_patch_author(): void
    {
        Sanctum::actingAs($this->admin);

        $author = Author::create([
            'name'    => 'Stephen King',
            'bio'     => 'Master of Horror',
            'country' => 'United States',
        ]);

        $payload = [
            'country' => 'USA',
        ];

        $response = $this->patchJson("/api/authors/{$author->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Author berhasil diperbarui',
                'data'    => [
                    'id'      => $author->id,
                    'name'    => 'Stephen King',
                    'country' => 'USA',
                ],
            ]);
    }

    /**
     * Test PUT /api/authors/{id} (update gagal dengan data tidak valid / 422)
     */
    public function test_update_author_fails_with_invalid_data(): void
    {
        Sanctum::actingAs($this->admin);

        $author = Author::create([
            'name'    => 'Ahmad Fuadi',
            'bio'     => 'Penulis Negeri 5 Menara',
            'country' => 'Indonesia',
        ]);

        // Kirim nama kosong
        $response = $this->putJson("/api/authors/{$author->id}", [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['name'],
            ]);
    }

    /**
     * Test PUT /api/authors/{id} (update ID tidak ditemukan / 404)
     */
    public function test_update_author_not_found(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/authors/99999', [
            'name' => 'Non Existent Author',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data Author tidak ditemukan',
            ]);
    }

    /**
     * Test DELETE /api/authors/{id} (destroy sukses)
     */
    public function test_can_destroy_author(): void
    {
        Sanctum::actingAs($this->admin);

        $author = Author::create([
            'name'    => 'Author Untuk Dihapus',
            'bio'     => 'Bio sementara',
            'country' => 'Indonesia',
        ]);

        $response = $this->deleteJson("/api/authors/{$author->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Author berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('authors', [
            'id' => $author->id,
        ]);
    }

    /**
     * Test DELETE /api/authors/{id} (destroy ID tidak ditemukan / 404)
     */
    public function test_destroy_author_not_found(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/authors/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data Author tidak ditemukan',
            ]);
    }
}
