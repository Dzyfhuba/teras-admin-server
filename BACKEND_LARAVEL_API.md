# Analisis Backend Laravel API dari Gambar dan Project

## Kesimpulan

Berdasarkan gambar halaman **Data Siswa** dan kode project yang sudah ada, backend Laravel API yang paling jelas dibutuhkan adalah modul administrasi siswa. Halaman tersebut menampilkan judul Data Siswa, tombol Import, serta menu Siswa, Rombel, Kenaikan Kelas, Data Guru, Bel Sekolah, dan Catatan Harian di sidebar.

Modul prioritas pertama adalah:

1. CRUD siswa.
2. Import siswa dari Excel.
3. Tahun ajaran.
4. Rombel dan anggota rombel.
5. Kenaikan, tinggal, mutasi, lulus, dan drop out siswa.
6. CRUD data guru.
7. Autentikasi dan hak akses.
8. Bel Sekolah dan Catatan Harian setelah struktur datanya ditentukan.

## 1. Manajemen Data Siswa

Backend perlu menyediakan endpoint berikut:

- `GET /api/students` untuk daftar siswa dengan pagination.
- `GET /api/students/{id}` untuk detail satu siswa.
- `POST /api/students` untuk menambah siswa.
- `PUT /api/students/{id}` untuk memperbarui seluruh data siswa.
- `PATCH /api/students/{id}` untuk memperbarui sebagian data siswa.
- `DELETE /api/students/{id}` untuk menghapus siswa.

Daftar siswa sebaiknya mendukung:

- `search` berdasarkan nama, NIS, atau NISN.
- Filter berdasarkan jenis kelamin.
- Filter berdasarkan asal sekolah.
- Filter berdasarkan rombel.
- Filter berdasarkan tahun ajaran.
- Filter berdasarkan status siswa.
- `sort` dan `direction`.
- Pagination melalui `page` dan `per_page`.

Data siswa mencakup:

- Nama lengkap.
- NIS.
- NISN.
- NIK.
- Jenis kelamin.
- Tempat dan tanggal lahir.
- Asal sekolah.
- Nomor KK.
- Alamat.
- Nomor telepon.
- Data ayah.
- Data ibu.
- Data wali.

Data tersebut sudah dipersiapkan pada model `app/Models/Student.php` dan migrasi `database/migrations/2026_11_19_005217_create_students_table.php`.

## 2. Import Excel Siswa

Tombol Import pada gambar seharusnya memanggil endpoint:

- `POST /api/student/import`

Request menggunakan `multipart/form-data` dengan field `file`.

Backend impor perlu:

- Memvalidasi bahwa file wajib ada.
- Membatasi tipe file ke `.xlsx`.
- Membatasi ukuran file.
- Membaca heading pada setiap sheet Excel.
- Memetakan kolom Excel ke field model `Student`.
- Membaca nama sheet sebagai nama rombel.
- Memisahkan nama sheet menjadi tingkat kelas dan nama kelas.
- Membuat atau mencari data rombel yang sesuai.
- Membuat relasi siswa ke rombel pada tahun ajaran aktif.
- Mengabaikan baris kosong.
- Mencegah duplikasi berdasarkan NIS atau NISN.
- Memvalidasi format tanggal, jenis kelamin, dan field wajib.
- Menjalankan proses dalam transaksi untuk mencegah data setengah tersimpan.
- Mengembalikan ringkasan jumlah berhasil, dilewati, dan gagal beserta nomor baris.

`app/Imports/StudentsImport.php` sudah mendefinisikan 23 sheet untuk kelas 1 sampai kelas 6. Contohnya:

- 1 Al Fajr.
- 1 Al Qomar.
- 2 Al Bayyinah.
- 6 Ar Rahman.
- 6 Al Insan.
- 6 As Saff.

Namun metode `model()` masih kosong. Implementasi tersebut belum memetakan data Excel dan belum membuat relasi ke `Section`.

Sebaiknya tersedia juga endpoint:

- `GET /api/students/import/template`

Endpoint tersebut mengirim template Excel yang berisi heading dan sheet rombel agar format impor konsisten dengan frontend.

## 3. Rombel

Sidebar menampilkan menu Rombel dan struktur project sudah memiliki model serta migrasi untuk kelas/rombel.

Endpoint yang sesuai:

- `GET /api/sections`
- `GET /api/sections/{id}`
- `POST /api/sections`
- `PUT /api/sections/{id}`
- `PATCH /api/sections/{id}`
- `DELETE /api/sections/{id}`
- `GET /api/sections/{id}/students`
- `POST /api/sections/{id}/students`
- `DELETE /api/sections/{section}/students/{student}`

Setiap rombel memiliki data:

- Tahun ajaran.
- Tingkat kelas 1 sampai 6.
- Nama kelas atau rombel.
- Wali kelas.

Rombel sebaiknya unik berdasarkan kombinasi:

- `academic_year_id`
- `grade`
- `name`

Model dan relasinya sudah dipersiapkan pada `app/Models/Section.php`:

- `academicYear()` mengarah ke tahun ajaran.
- `waliKelas()` mengarah ke employee/guru.

## 4. Tahun Ajaran

Tahun ajaran diperlukan untuk membedakan rombel antar periode. Backend perlu menyediakan:

- `GET /api/academic-years`
- `GET /api/academic-years/{id}`
- `POST /api/academic-years`
- `PUT /api/academic-years/{id}`
- `PATCH /api/academic-years/{id}`
- `DELETE /api/academic-years/{id}`
- `PATCH /api/academic-years/{id}/activate`

Data yang tersedia:

- Nama, misalnya `2026/2027`.
- Tanggal mulai.
- Tanggal selesai.

Model sudah ada pada `app/Models/AcademicYear.php` dan migrasinya pada `database/migrations/2026_11_19_014315_create_academic_years_table.php`.

## 5. Relasi Siswa dan Rombel

Siswa tidak sebaiknya memiliki satu kolom tetap untuk kelas karena riwayat kelas perlu disimpan dari tahun ke tahun.

Gunakan tabel pivot `student_sections` untuk menyimpan:

- Siswa.
- Rombel.
- Status.
- Tanggal pembuatan dan pembaruan.

Status yang dapat digunakan:

- `aktif`
- `lulus`
- `tinggal kelas`
- `mutasi keluar`
- `mutasi masuk`
- `drop out`

Endpoint tambahan:

- `GET /api/students/{id}/sections`
- `POST /api/students/{id}/sections`
- `PATCH /api/student-sections/{id}/status`

Endpoint status harus mencegah siswa aktif memiliki dua rombel dalam satu tahun ajaran yang sama.

## 6. Kenaikan Kelas

Menu Kenaikan Kelas memerlukan endpoint:

- `GET /api/promotions/preview`
- `POST /api/promotions/preview`
- `POST /api/promotions`
- `POST /api/promotions/{promotion}/undo`

Fitur kenaikan kelas sebaiknya mendukung:

- Memilih tahun ajaran sumber.
- Memilih rombel sumber.
- Memilih tahun ajaran tujuan.
- Memilih rombel tujuan.
- Pilihan kenaikan, tinggal kelas, lulus, atau mutasi.
- Menjamin status lama menjadi tidak aktif sebelum membuat status baru.
- Menjalankan seluruh operasi dalam satu transaksi database.
- Menyimpan riwayat supaya proses dapat dilacak dan dibatalkan.

Untuk siswa kelas 6, pilihan proses utama adalah lulus. Untuk siswa kelas 1 sampai 5, pilihan utama adalah kenaikan kelas.

## 7. Data Guru

Menu Data Guru memerlukan CRUD berdasarkan model `Employee`.

Endpoint:

- `GET /api/employees`
- `GET /api/employees/{id}`
- `POST /api/employees`
- `PUT /api/employees/{id}`
- `PATCH /api/employees/{id}`
- `DELETE /api/employees/{id}`
- `GET /api/employees/available-for-homeroom`

Data guru mencakup:

- Nama lengkap.
- Gelar depan dan gelar belakang.
- Kategori guru, tendik, atau non-satmikal.
- NIK.
- NIP.
- NUPTK.
- NIY.
- Jenis kelamin.
- Tempat dan tanggal lahir.
- Alamat.
- Telepon.
- Pendidikan terakhir.
- Jabatan.
- TMT jabatan.
- Status aktif.
- Akun pengguna, jika guru memiliki akses login.

Data tersebut tersedia pada `app/Models/Employee.php` dan `database/migrations/2026_11_19_014316_create_employees_table.php`.

## 8. Bel Sekolah

Menu Bel Sekolah terlihat pada sidebar, tetapi project belum memiliki model atau migrasi yang jelas untuk modul ini.

Backend yang diperlukan setelah struktur disepakati:

- `GET /api/school-bells`
- `GET /api/school-bells/{id}`
- `POST /api/school-bells`
- `PATCH /api/school-bells/{id}`
- `DELETE /api/school-bells/{id}`
- `POST /api/school-bells/{id}/test`

Kemungkinan data:

- Nama jadwal.
- Hari.
- Jam.
- Durasi atau urutan nada.
- Aktif/nonaktif.
- Jadwal istirahat.
- Jadwal pulang.
- Pengecualian tanggal.

Fitur ringing otomatis sebaiknya diproses oleh worker atau scheduler server, bukan oleh browser frontend.

## 9. Catatan Harian

Menu Catatan Harian juga terlihat tetapi belum memiliki implementasi atau struktur database di project.

Backend yang sesuai:

- `GET /api/daily-notes`
- `GET /api/daily-notes/{id}`
- `POST /api/daily-notes`
- `PATCH /api/daily-notes/{id}`
- `DELETE /api/daily-notes/{id}`

Kemungkinan filter:

- Tanggal.
- Siswa.
- Rombel.
- Guru pencatat.
- Kategori.
- Status.

Kemungkinan data:

- Judul.
- Isi catatan.
- Tanggal.
- Siswa atau rombel.
- Penulis/guru.
- Jenis catatan.

## 10. Autentikasi dan Otorisasi

Route API sebaiknya menggunakan Laravel Sanctum.

Contoh struktur route:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('students', StudentsController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('academic-years', AcademicYearController::class);
});
```

Hak akses minimal:

- Administrator.
- Kepala sekolah.
- Guru/wali kelas.
- Staff administrasi.

Paling tidak pisahkan hak untuk:

- Membuat dan mengubah siswa.
- Menghapus siswa.
- Melakukan impor massal.
- Melakukan kenaikan kelas.
- Mengubah rombel.
- Mengubah data guru.
- Mengelola Bel Sekolah.
- Mengelola Catatan Harian.

## 11. Format Response API

Untuk konsistensi, gunakan format JSON yang seragam.

Berhasil:

```json
{
  "message": "Data berhasil disimpan",
  "data": {}
}
```

List/pagination:

```json
{
  "message": "Data berhasil diambil",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 0
  }
}
```

Gagal validasi:

```json
{
  "message": "Validation failed",
  "errors": {
    "nama_lengkap": ["Nama lengkap wajib diisi"]
  }
}
```

## 12. Masalah Kode yang Perlu Diperbaiki

1. `database/migrations/2026_11_19_025213_create_student_sections_table.php` menggunakan `App\Models\Students::class`, tetapi model yang tersedia bernama `Student`.

2. Migrasi harus memperbaiki foreign key agar mengarah ke tabel `students`, bukan nama model atau tabel yang salah.

3. Model `Student` belum menggunakan `SoftDeletes`, sedangkan tabel migration memiliki kolom `deleted_at`.

4. Fillable `Student` belum mencakup:
   - `ayah_nik`
   - `ibu_nik`
   - `wali_nik`

5. Migrasi memiliki data ayah, ibu, dan wali yang lengkap, tetapi model tidak mencakup seluruh data NIK.

6. `StudentsImport::model()` belum memetakan satu pun baris Excel ke model.

7. `BeforeSheet` menyimpan nama sheet ke `$currentSheet`, tetapi sheet tersebut belum dipakai untuk menentukan rombel atau tahun ajaran.

8. `SectionController`, `EmployeeController`, dan `AcademicYearController` masih kosong.

9. Route import belum memakai middleware autentikasi.

10. `StudentSection` menggunakan key `students_id`, sedangkan implementasi Laravel yang lazim dapat menggunakan `student_id` agar konsisten.

## 13. Urutan Implementasi

1. Perbaiki migrasi dan relasi siswa–rombel.
2. Tambahkan soft delete pada siswa.
3. Lengkapi fillable dan cast model.
4. Buat request validation terpisah untuk create dan update siswa.
5. Implementasikan CRUD dan pagination siswa.
6. Implementasikan import Excel beserta template.
7. Buat CRUD tahun ajaran.
8. Buat CRUD rombel.
9. Tambahkan relasi siswa ke rombel dan status siswa.
10. Implementasikan preview dan eksekusi kenaikan kelas.
11. Implementasikan CRUD employee/guru.
12. Tambahkan autentikasi Sanctum dan authorization.
13. Implementasikan Bel Sekolah dan Catatan Harian.

## 14. Batas Kesimpulan dari Gambar

Menu Bel Sekolah dan Catatan Harian dapat diidentifikasi namanya, tetapi detail input, relasi, dan alur pengguna belum dapat dipastikan dari gambar.

Sebaliknya, fitur berikut dapat ditentukan secara kuat dari gambar dan project:

- Manajemen siswa.
- Import siswa.
- Rombel.
- Kenaikan kelas.
- Data guru.
- Tahun ajaran.
- Riwayat rombel dan status siswa.
