<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'title_depan',
        'title_belakang',
        'category',
        'nik',
        'nip',
        'nuptk',
        'niy',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'telepon',
        'pendidikan_terakhir',
        'jabatan',
        'tmt_jabatan',
        'is_active',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'wali_kelas_id');
    }

    public function dailyNotes(): HasMany
    {
        return $this->hasMany(DailyNote::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function ofCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    #[Scope]
    protected function search(Builder $query, string $term): Builder
    {
        $like = "%{$term}%";

        return $query->where(function (Builder $builder) use ($like): void {
            $builder->where('nama_lengkap', 'like', $like)
                ->orWhere('nik', 'like', $like)
                ->orWhere('nip', 'like', $like)
                ->orWhere('nuptk', 'like', $like)
                ->orWhere('niy', 'like', $like)
                ->orWhere('jabatan', 'like', $like);
        });
    }
}
