<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentSection extends Pivot
{
    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'students_id',
        'section_id',
        'status',
    ];
}
