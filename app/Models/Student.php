<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_lengkap',
        'nis',
        'asal_sekolah',
        'nisn',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'kk',
        'alamat',
        'telepon',
        'ayah_nama',
        'ayah_nik',
        'ayah_pendidikan',
        'ayah_pekerjaan',
        'ayah_tempat_lahir',
        'ayah_tanggal_lahir',
        'ayah_penghasilan',
        'ayah_telepon',
        'ibu_nama',
        'ibu_nik',
        'ibu_pendidikan',
        'ibu_pekerjaan',
        'ibu_tempat_lahir',
        'ibu_tanggal_lahir',
        'ibu_penghasilan',
        'ibu_telepon',
        'wali_nama',
        'wali_nik',
        'wali_pendidikan',
        'wali_pekerjaan',
        'wali_tempat_lahir',
        'wali_tanggal_lahir',
        'wali_penghasilan',
        'wali_telepon',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'ayah_tanggal_lahir' => 'date',
        'ibu_tanggal_lahir' => 'date',
        'wali_tanggal_lahir' => 'date',
    ];

    public function studentSections(): HasMany
    {
        return $this->hasMany(StudentSection::class);
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'student_sections')
            ->withPivot(['status'])
            ->withTimestamps();
    }

    public function dailyNotes(): HasMany
    {
        return $this->hasMany(DailyNote::class);
    }

    public function promotionItems(): HasMany
    {
        return $this->hasMany(PromotionItem::class);
    }
}
