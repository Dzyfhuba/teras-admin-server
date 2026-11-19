<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
	use SoftDeletes;

	protected $fillable = [
		'name',
		'date_start',
		'date_end',
	];

	protected $casts = [
		'date_start' => 'date',
		'date_end' => 'date',
	];
}
