# Daftar 14 Tugas Backend Laravel

Dokumen ini adalah ringkasan pembagian pekerjaan berdasarkan `BACKEND_LARAVEL_API.md`.

| ID | Urutan | Tugas | Prioritas | Dependensi |
|---|---:|---|---|---|
| `task-01-foundation-database` | 1 | Perbaikan Foundation Database | Tinggi | Tidak ada |
| `task-02-auth-response` | 2 | Autentikasi, Response, dan Middleware | Tinggi | Tugas 1 |
| `task-03-academic-year` | 3 | CRUD Tahun Ajaran | Tinggi | Tugas 1, 2 |
| `task-04-student-crud` | 4 | CRUD Data Siswa | Tinggi | Tugas 1, 2 |
| `task-05-student-excel-import` | 5 | Import Excel Siswa | Tinggi | Tugas 4, 7 |
| `task-06-excel-template` | 6 | Template Import Excel | Sedang | Tugas 4, 7 |
| `task-07-section-crud` | 7 | CRUD Rombel | Tinggi | Tugas 3, 9 |
| `task-08-student-section` | 8 | Hubungan Siswa dan Rombel | Tinggi | Tugas 4, 7 |
| `task-09-employee-crud` | 9 | CRUD Data Guru | Tinggi | Tugas 1, 2 |
| `task-10-promotion-status` | 10 | Kenaikan dan Status Siswa | Tinggi | Tugas 8 |
| `task-11-promotion-execution` | 11 | Eksekusi dan Pembatalan Kenaikan Kelas | Tinggi | Tugas 10 |
| `task-12-daily-notes` | 12 | Catatan Harian | Sedang | Tugas 3, 4, 7, 9 |
| `task-13-school-bell` | 13 | Bel Sekolah | Sedang | Tugas 2 |
| `task-14-testing-integration` | 14 | Pengujian dan Integrasi API | Tinggi | Tugas 3 sampai 13 |

## Instruksi Impor ke Firestore

File yang digunakan untuk impor adalah `tugas_backend_laravel.json`.

File JSON tersebut berisi array. Setiap objek harus diimpor sebagai satu dokumen.

Gunakan `id` sebagai ID dokumen dan nama collection:

```text
tasks
```

Jangan menggunakan nomor urut `1`, `2`, dan seterusnya sebagai nama collection atau ID dokumen. Gunakan nilai `id` penuh agar referensi pada field `dependencies` tetap valid.

Contoh target dokumen:

```text
tasks/task-01-foundation-database
tasks/task-02-auth-response
tasks/task-03-academic-year
```

## Cara Impor Menggunakan Firebase CLI

Jika tersedia Firebase CLI yang login ke project yang benar:

1. Buka direktori root project.
2. Pastikan service account atau akun Firebase sudah memiliki izin Firestore.
3. Jalankan:

```sh
firebase use --add
```

4. Pilih project Firebase yang tepat.
5. Gunakan script atau tool impor Firestore yang mendukung JSON array.

Firebase CLI tidak menyediakan perintah impor JSON mentah yang langsung memakai setiap field `id` sebagai document ID. Gunakan Node.js dan Firebase Admin untuk impor aman.

## Contoh Script Node.js

Simpan script sebagai file sementara, misalnya `import_tasks.js`, lalu jalankan:

```js
const admin = require("firebase-admin");
const fs = require("fs");

admin.initializeApp({
  credential: admin.credential.applicationDefault(),
});

const db = admin.firestore();
const tasks = JSON.parse(
  fs.readFileSync("tugas_backend_laravel.json", "utf8")
);

(async () => {
  const batch = db.batch();

  tasks.forEach((task) => {
    const documentRef = db.collection("tasks").doc(task.id);
    batch.set(documentRef, task, { merge: true });
  });

  await batch.commit();
  console.log(`Berhasil mengimpor ${tasks.length} tugas`);
})();
```

Jalankan dengan:

```sh
npm install firebase-admin
export GOOGLE_APPLICATION_CREDENTIALS="/absolute/path/service-account.json"
node import_tasks.js
```

Untuk PowerShell, set credential menggunakan:

```powershell
$env:GOOGLE_APPLICATION_CREDENTIALS="D:\absolute\path\service-account.json"
node import_tasks.js
```

## Aturan Penting

- Import ke collection `tasks`, bukan ke collection lain.
- Gunakan field `id` sebagai document ID.
- Jangan mengubah nama `id` setelah tugas memiliki referensi pada `dependencies`.
- Checklist tetap berada sebagai array of object di dalam dokumen.
- Gunakan `false` untuk semua `completed` saat impor awal.
- Gunakan `merge: true` agar impor ulang tidak menghapus field lain yang sedang dikelola aplikasi.
- Jangan memperbarui seluruh data Firestore secara manual melalui JSON jika dokumen sudah memiliki status progres.
- Pastikan Firestore Rules memperbolehkan admin atau script service account melakukan batch write.
- Jalankan impor sekali terlebih dahulu pada project Firebase yang benar.
- Verifikasi jumlah dokumen collection `tasks` menjadi 14.
- Jangan jalankan script impor ulang sebagai pengganti sinkronisasi status pekerjaan.

## Urutan Pengerjaan

1. Tugas 1 dan 2 terlebih dahulu.
2. Tugas 3, 4, 9, dan 13 dapat dimulai setelah foundation tersedia.
3. Tugas 7 membutuhkan tugas 3 dan 9.
4. Tugas 8 membutuhkan tugas 4 dan 7.
5. Tugas 5 membutuhkan tugas 4 dan 7.
6. Tugas 6 membutuhkan tugas 4 dan 7.
7. Tugas 10 membutuhkan tugas 8.
8. Tugas 11 membutuhkan tugas 10.
9. Tugas 12 membutuhkan tugas 3, 4, 7, dan 9.
10. Tugas 14 dilakukan setelah tugas 3 sampai 13 tersedia.
