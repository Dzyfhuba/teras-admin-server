<?php

namespace App\Models;

use Database\Factories\DailyNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyNote extends Model
{
    /** @use HasFactory<DailyNoteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'section_id',
        'employee_id',
        'academic_year_id',
        'date',
        'title',
        'content',
        'category',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
