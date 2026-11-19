<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
    protected $fillable = [
        'nama_lengkap',
        'nis',
        'asal_sekolah',
        'nisn',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'nomor_kartu_keluarga',
        'alamat',
        'telepon',
        'ayah_nama',
        'ayah_pendidikan',
        'ayah_pekerjaan',
        'ayah_tempat_lahir',
        'ayah_tanggal_lahir',
        'ayah_penghasilan',
        'ayah_telepon',
        'ibu_nama',
        'ibu_pendidikan',
        'ibu_pekerjaan',
        'ibu_tempat_lahir',
        'ibu_tanggal_lahir',
        'ibu_penghasilan',
        'ibu_telepon',
        'wali_nama',
        'wali_pendidikan',
        'wali_pekerjaan',
        'wali_tempat_lahir',
        'wali_tanggal_lahir',
        'wali_penghasilan',
        'wali_telepon',
    ];
}
