<?php

namespace App\Models;

use Database\Factories\PromotionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionItem extends Model
{
    /** @use HasFactory<PromotionItemFactory> */
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'student_id',
        'source_student_section_id',
        'target_section_id',
        'action',
        'status',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function sourceStudentSection()
    {
        return $this->belongsTo(StudentSection::class, 'source_student_section_id');
    }

    public function targetSection()
    {
        return $this->belongsTo(Section::class, 'target_section_id');
    }
}
