<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'academic_year_id',
        'grade',
        'name',
        'wali_kelas_id',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'wali_kelas_id');
    }

    public function studentSections(): HasMany
    {
        return $this->hasMany(StudentSection::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_sections')
            ->withPivot(['status'])
            ->withTimestamps();
    }

    public function dailyNotes(): HasMany
    {
        return $this->hasMany(DailyNote::class);
    }
}
