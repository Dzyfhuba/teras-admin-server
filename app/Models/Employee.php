<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
