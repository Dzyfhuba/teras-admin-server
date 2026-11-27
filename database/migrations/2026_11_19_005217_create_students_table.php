<?php

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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nis')->unique()->nullable();
            $table->string('asal_sekolah')->comment('TK / SD / SMP')->nullable();
            $table->string('nisn')->nullable();
            $table->string('nik')->nullable();
            $table->string('jenis_kelamin')->comment('Laki-laki / Perempuan')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('kk')->nullable();
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('ayah_nama')->nullable();
            $table->string('ayah_nik')->nullable();
            $table->string('ayah_pendidikan')->nullable();
            $table->string('ayah_pekerjaan')->nullable();
            $table->string('ayah_tempat_lahir')->nullable();
            $table->string('ayah_tanggal_lahir')->nullable();
            $table->string('ayah_penghasilan')->nullable();
            $table->string('ayah_telepon')->nullable();
            $table->string('ibu_nama')->nullable();
            $table->string('ibu_nik')->nullable();
            $table->string('ibu_pendidikan')->nullable();
            $table->string('ibu_pekerjaan')->nullable();
            $table->string('ibu_tempat_lahir')->nullable();
            $table->string('ibu_tanggal_lahir')->nullable();
            $table->string('ibu_penghasilan')->nullable();
            $table->string('ibu_telepon')->nullable();
            $table->string('wali_nama')->nullable();
            $table->string('wali_nik')->nullable();
            $table->string('wali_pendidikan')->nullable();
            $table->string('wali_pekerjaan')->nullable();
            $table->string('wali_tempat_lahir')->nullable();
            $table->string('wali_tanggal_lahir')->nullable();
            $table->string('wali_penghasilan')->nullable();
            $table->string('wali_telepon')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
