# Backend Administrasi Sekolah

Backend ini adalah aplikasi Laravel untuk menyediakan API administrasi sekolah. API mencakup pengelolaan siswa, tahun ajaran, rombongan belajar (rombel), relasi dan status siswa, data guru/pegawai, kenaikan kelas, catatan harian, dan bel sekolah. Fitur pendukungnya meliputi impor data siswa dari Excel dan template impor.

Endpoint API berada di prefix `/api` dan didefinisikan di `routes/api.php`. Autentikasi endpoint pengguna menggunakan Laravel Sanctum.

## Persyaratan

- PHP 8.3 atau lebih baru beserta ekstensi PHP yang dibutuhkan Laravel.
- Composer.
- Node.js 24.12 atau lebih baru dan npm.
- Git.

Konfigurasi bawaan memakai SQLite. MySQL atau MariaDB juga dapat digunakan dengan mengubah konfigurasi database di `.env`.

## Instalasi dan Menjalankan Aplikasi

Jalankan perintah berikut dari direktori proyek di PowerShell:

```powershell
composer run setup
```

Script `setup` akan memasang dependency PHP dan JavaScript, membuat `.env` dari `.env.example` jika belum ada, membuat `APP_KEY`, menjalankan migrasi database, dan membangun aset frontend. Dengan konfigurasi bawaan, database SQLite berada di `database/database.sqlite`.

Setelah setup selesai, jalankan server Laravel:

```powershell
php artisan serve
```

Buka `http://127.0.0.1:8000`. Endpoint API tersedia pada alamat yang sama dengan prefix `/api`, misalnya `http://127.0.0.1:8000/api/students`.

Untuk pengembangan aset frontend dengan hot reload, jalankan perintah berikut di terminal terpisah:

```powershell
npm run dev
```

Jika menggunakan MySQL atau MariaDB, buat database terlebih dahulu, atur `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`, lalu jalankan:

```powershell
php artisan migrate
```

Jangan membagikan atau meng-commit `.env` maupun credential database.

## Menjalankan Pengujian

```powershell
php artisan test
```

## Daftar Task dan Kontributor

Checklist di bawah mengikuti urutan task. Kotak yang belum dicentang menunjukkan status penyelesaian belum dicatat di README.

- [ ] **task-01-foundation-database** — Perbaikan Foundation Database — M Ferdli
- [ ] **task-02-auth-response** — Autentikasi, Response, dan Middleware — Muhammad Alyan
- [ ] **task-03-academic-year** — CRUD Tahun Ajaran — Muh Tegar R
- [ ] **task-04-student-crud** — CRUD Data Siswa — Ahmad Farrel
- [ ] **task-05-student-excel-import** — Import Excel Siswa — Muhammad Ilham R
- [ ] **task-06-excel-template** — Template Import Excel — Azzahra Nabila
- [ ] **task-07-section-crud** — CRUD Rombel — Mutiara Mawar
- [ ] **task-08-student-section** — Hubungan Siswa dan Rombel — Vidya Sulton
- [ ] **task-09-employee-crud** — CRUD Data Guru — Haydar Yahya
- [ ] **task-10-promotion-status** — Kenaikan dan Status Siswa — Ardia Rahma
- [ ] **task-11-promotion-execution** — Eksekusi dan Pembatalan Kenaikan Kelas — Annisa Salsa
- [ ] **task-12-daily-notes** — Catatan Harian — Ardika Ikbar
- [ ] **task-13-school-bell** — Bel Sekolah — Faidlur Rizqi
- [ ] **task-14-testing-integration** — Pengujian dan Integrasi API — Hafidz Ubaidillah
