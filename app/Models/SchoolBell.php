<?php

namespace App\Models;

use Database\Factories\SchoolBellFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolBell extends Model
{
    /** @use HasFactory<SchoolBellFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'day',
        'time',
        'duration_seconds',
        'schedule_type',
        'exception_date',
        'is_active',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'is_active' => 'boolean',
    ];
}
