# Tugas Framework Laravel 6 — Implementasi CRUD Transaksi, Eager Loading Relasi, dan Role-Based Middleware (Admin & Customer)

Tugas mata kuliah Pemrograman Framework Laravel ke-6 (Lanjutan): Implementasi **CRUD Tabel Transaksi**, **Relasi Database & Model (One-to-Many & Many-to-One)**, **Eager Loading (`with()`)**, serta penegakan hak akses berbasis role menggunakan **Middleware Custom (`IsAdmin` & `IsCustomer`)** pada REST API "API Book Sales".

---

## 📋 Informasi Mahasiswa & Repositori
- **Mahasiswa**: MUHAMMAD RIZKI (NIM: 064102400020)
- **Instansi**: Universitas Trisakti
- **Nama Repositori**: `FL-MUHAMMADRIZKI_UNIVTRISAKTI_TUGAS-KE-6`
- **GitHub URL**: [https://github.com/064102400020-bit/FL-MUHAMMADRIZKI_UNIVTRISAKTI_TUGAS-KE-6.git](https://github.com/064102400020-bit/FL-MUHAMMADRIZKI_UNIVTRISAKTI_TUGAS-KE-6.git)
- **Tautan Controller & Middleware Utama di GitHub**:
  - [TransaksiController.php](https://github.com/064102400020-bit/FL-MUHAMMADRIZKI_UNIVTRISAKTI_TUGAS-KE-6/blob/main/app/Http/Controllers/TransaksiController.php)
  - [IsCustomer.php](https://github.com/064102400020-bit/FL-MUHAMMADRIZKI_UNIVTRISAKTI_TUGAS-KE-6/blob/main/app/Http/Middleware/IsCustomer.php)
  - [IsAdmin.php](https://github.com/064102400020-bit/FL-MUHAMMADRIZKI_UNIVTRISAKTI_TUGAS-KE-6/blob/main/app/Http/Middleware/IsAdmin.php)
  - [Transaksi.php (Model)](https://github.com/064102400020-bit/FL-MUHAMMADRIZKI_UNIVTRISAKTI_TUGAS-KE-6/blob/main/app/Models/Transaksi.php)
- **Lokasi File Route API**:
  - `routes/api.php`

---

## 🛠️ Fitur & Ketentuan Pengerjaan Tugas Lanjutan

1. **Struktur Tabel & Relasi Database (`transaksis`)**:
   - Dibuat melalui migrasi `create_transaksis_table` dengan relasi foreign key:
     - `user_id` -> constrained ke `users` dengan `cascadeOnDelete()`.
     - `book_id` -> constrained ke `books` dengan `cascadeOnDelete()`.
   - Kolom `total_harga` dihitung otomatis secara server-side pada controller (`harga_buku × jumlah_beli`) untuk menjamin integritas data (tidak mempercayai input angka total dari client).
2. **Relasi Model Eloquent**:
   - `Transaksi` `belongsTo(User::class)` dan `belongsTo(Book::class)`.
   - `User` `hasMany(Transaksi::class)`.
   - `Book` `hasMany(Transaksi::class)`.
3. **Eager Loading (`with()`)**:
   - Seluruh endpoint pengambilan data transaksi (`index` dan `show`) menerapkan eager loading `with(['user', 'book'])` untuk mengeliminasi masalah N+1 Query dan menampilkan data lengkap buku dan customer.
4. **Pemisahan Middleware & Aturan Akses Role**:
   - **Customer (`auth:sanctum` + `customer`)**:
     - `POST /api/transaksi` (Create transaksi baru).
     - `GET /api/transaksi/{id}` (Melihat detail transaksi miliknya sendiri; jika mencoba melihat transaksi customer lain, dibalas **403 Forbidden**).
     - `PUT/PATCH /api/transaksi/{id}` (Mengubah transaksi miliknya sendiri; jika mencoba mengubah milik orang lain, dibalas **403 Forbidden**).
   - **Admin (`auth:sanctum` + `admin`)**:
     - `GET /api/transaksi` (Melihat semua transaksi seluruh customer beserta relasinya).
     - `DELETE /api/transaksi/{id}` (Menghapus data transaksi).
   - **Publik**:
     - `POST /api/register` (Role default otomatis adalah `'customer'`).
     - `POST /api/login`.
     - `GET /api/authors`, `GET /api/genres`, `GET /api/books` (Index & Show).
5. **Format Respons JSON Konsisten**:
   - Format: `{ "success": boolean, "message": string, "data": ... }`.
   - HTTP Status: `200 OK`, `201 Created`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable Content`.

---

## 🗄️ Skema Database

### Tabel `transaksis`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Primary Key, Auto Increment |
| `user_id` | BIGINT UNSIGNED | Foreign Key -> `users(id)` ON DELETE CASCADE |
| `book_id` | BIGINT UNSIGNED | Foreign Key -> `books(id)` ON DELETE CASCADE |
| `jumlah` | INT | Jumlah buku yang dibeli (min 1) |
| `total_harga` | DECIMAL(12,2) | Dihitung di backend: `harga_buku × jumlah` |
| `status` | VARCHAR(255) | Status transaksi: `pending`, `paid`, `cancelled` (Default: `pending`) |
| `created_at` / `updated_at` | TIMESTAMP | Waktu pembuatan & perubahan |

---

## 🚀 Panduan Menjalankan Proyek

1. **Setup Awal**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

2. **Migrasi dan Seeder Database**:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Akun default untuk testing:*
   - **Admin**: `admin@example.com` / `password` (Role: `admin`)
   - **Customer 1**: `customer1@example.com` / `password` (Role: `customer`)
   - **Customer 2**: `customer2@example.com` / `password` (Role: `customer`)

3. **Jalankan Server**:
   ```bash
   php artisan serve
   ```

4. **Jalankan Automated Tests**:
   ```bash
   php artisan test
   ```
   *Hasil: 56 passing tests (214 assertions).*

---

## 📮 Koleksi Postman
File `postman_collection.json` tersedia di root repository dengan 5 folder pengujian lengkap (Authentication, Authors, Genres, Books, Transaksi) beserta penanganan Bearer Token otomatis.
