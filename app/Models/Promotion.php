<?php

namespace App\Models;

use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasFactory;

    protected $fillable = [
        'source_academic_year_id',
        'target_academic_year_id',
        'source_section_id',
        'target_section_id',
        'employee_id',
        'status',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function sourceAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'source_academic_year_id');
    }

    public function targetAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'target_academic_year_id');
    }

    public function sourceSection()
    {
        return $this->belongsTo(Section::class, 'source_section_id');
    }

    public function targetSection()
    {
        return $this->belongsTo(Section::class, 'target_section_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(PromotionItem::class);
    }
}
