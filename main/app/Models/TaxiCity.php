<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxiCity extends Model
{
	protected $fillable = [
		'type',
		'taxi_id',
		'city_id',
		'value',
		'company_id'
	];
	public function city()
	{
		return $this->belongsTo(Municipality::class, 'city_id');
	}

	public function municipality()
	{
		return $this->belongsTo(Municipality::class, 'city_id');
	}
	public function taxi()
	{
		return $this->belongsTo(Taxi::class);
	}
}
