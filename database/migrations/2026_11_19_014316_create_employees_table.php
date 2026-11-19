<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete()->nullOnDelete();
            $table->string('nama_lengkap');
            $table->string('title_depan')->nullable();
            $table->string('title_belakang')->nullable();
            $table->string('category')->comment('guru / tendik / non-satmikal')->nullable();
            $table->string('nik')->nullable()->comment('Nomor Induk Kependudukan');
            $table->string('nip')->nullable()->comment('Nomor Induk Pegawai (Dari Dinas Pendidikan)');
            $table->string('nuptk')->nullable()->comment('Nomor Unik Pendidik dan Tenaga Kependidikan (Dari Dinas Pendidikan)');
            $table->string('niy')->nullable()->comment('Nomor Induk Yayasan');
            $table->string('jenis_kelamin')->comment('Laki-laki / Perempuan')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('pendidikan_terakhir')->nullable()->comment('SD / SMP / SMA / S1 / S2 / S3');
            $table->string('jabatan')->nullable()->comment('Guru / Kepala Sekolah / Staff / dll');
            $table->string('tmt_jabatan')->nullable()->comment('Tanggal Mulai Tugas Jabatan');
            $table->boolean('is_active')->default(true)->comment('Apakah masih aktif bekerja?');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
