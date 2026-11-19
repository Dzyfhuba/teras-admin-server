<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
