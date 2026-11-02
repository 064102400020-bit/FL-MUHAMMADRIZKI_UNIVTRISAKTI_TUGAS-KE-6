<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GenreApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Admin Genre Test',
            'email'    => 'admin@genre.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);
    }

    /**
     * Test GET /api/genres (index)
     */
    public function test_can_get_all_genres(): void
    {
        Genre::create(['name' => 'Fiksi', 'description' => 'Cerita fiksi']);

        $response = $this->getJson('/api/genres');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data genre berhasil diambil',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'description', 'created_at', 'updated_at'],
                ],
            ]);
    }

    /**
     * Test POST /api/genres (store)
     */
    public function test_can_create_genre(): void
    {
        Sanctum::actingAs($this->admin);

        $payload = [
            'name'        => 'Fantasy',
            'description' => 'Cerita tentang dunia sihir',
        ];

        $response = $this->postJson('/api/genres', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data genre berhasil dibuat',
                'data'    => [
                    'name'        => 'Fantasy',
                    'description' => 'Cerita tentang dunia sihir',
                ],
            ]);

        $this->assertDatabaseHas('genres', [
            'name' => 'Fantasy',
        ]);
    }

    /**
     * Test GET /api/genres/{id} (show sukses)
     */
    public function test_can_show_genre_by_id(): void
    {
        $genre = Genre::create([
            'name'        => 'Horor',
            'description' => 'Kisah menakutkan',
        ]);

        $response = $this->getJson("/api/genres/{$genre->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Genre berhasil ditemukan',
                'data'    => [
                    'id'          => $genre->id,
                    'name'        => 'Horor',
                    'description' => 'Kisah menakutkan',
                ],
            ]);
    }

    /**
     * Test GET /api/genres/{id} (show gagal / 404)
     */
    public function test_show_genre_not_found(): void
    {
        $response = $this->getJson('/api/genres/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data Genre tidak ditemukan',
            ]);
    }

    /**
     * Test PUT /api/genres/{id} (update sukses data valid)
     */
    public function test_can_update_genre_with_valid_data(): void
    {
        Sanctum::actingAs($this->admin);

        $genre = Genre::create([
            'name'        => 'Misteri Lama',
            'description' => 'Deskripsi lama',
        ]);

        $payload = [
            'name'        => 'Misteri Baru',
            'description' => 'Deskripsi diperbarui',
        ];

        $response = $this->putJson("/api/genres/{$genre->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Genre berhasil diperbarui',
                'data'    => [
                    'id'          => $genre->id,
                    'name'        => 'Misteri Baru',
                    'description' => 'Deskripsi diperbarui',
                ],
            ]);

        $this->assertDatabaseHas('genres', [
            'id'   => $genre->id,
            'name' => 'Misteri Baru',
        ]);
    }

    /**
     * Test PATCH /api/genres/{id} (update sebagian data)
     */
    public function test_can_patch_genre(): void
    {
        Sanctum::actingAs($this->admin);

        $genre = Genre::create([
            'name'        => 'Komedi',
            'description' => 'Cerita lucu',
        ]);

        $payload = [
            'description' => 'Deskripsi komedi terupdate',
        ];

        $response = $this->patchJson("/api/genres/{$genre->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Genre berhasil diperbarui',
                'data'    => [
                    'id'          => $genre->id,
                    'name'        => 'Komedi',
                    'description' => 'Deskripsi komedi terupdate',
                ],
            ]);
    }

    /**
     * Test PUT /api/genres/{id} (update gagal dengan data tidak valid / 422)
     */
    public function test_update_genre_fails_with_invalid_data(): void
    {
        Sanctum::actingAs($this->admin);

        Genre::create(['name' => 'Genre Unik Lainnya']);

        $genre = Genre::create([
            'name'        => 'Thriller',
            'description' => 'Cerita menegangkan',
        ]);

        // Kirim nama kosong (tidak valid)
        $response = $this->putJson("/api/genres/{$genre->id}", [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['name'],
            ]);

        // Kirim nama duplikat (sudah dipakai record lain)
        $responseDuplicate = $this->putJson("/api/genres/{$genre->id}", [
            'name' => 'Genre Unik Lainnya',
        ]);

        $responseDuplicate->assertStatus(422);
    }

    /**
     * Test PUT /api/genres/{id} (update ID tidak ditemukan / 404)
     */
    public function test_update_genre_not_found(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/genres/99999', [
            'name' => 'Non Existent Genre',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data Genre tidak ditemukan',
            ]);
    }

    /**
     * Test DELETE /api/genres/{id} (destroy sukses)
     */
    public function test_can_destroy_genre(): void
    {
        Sanctum::actingAs($this->admin);

        $genre = Genre::create([
            'name'        => 'Genre Dihapus',
            'description' => 'Untuk dihapus',
        ]);

        $response = $this->deleteJson("/api/genres/{$genre->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Genre berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    /**
     * Test DELETE /api/genres/{id} (destroy ID tidak ditemukan / 404)
     */
    public function test_destroy_genre_not_found(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/genres/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data Genre tidak ditemukan',
            ]);
    }
}
