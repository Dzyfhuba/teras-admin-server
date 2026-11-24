<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'date_start',
        'date_end',
        'is_active',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'is_active' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function dailyNotes(): HasMany
    {
        return $this->hasMany(DailyNote::class);
    }
}
