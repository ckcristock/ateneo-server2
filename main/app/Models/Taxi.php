<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taxi extends Model
{
	protected $fillable = [
		'route'
	];

	public function taxiCities()
	{
		return $this->hasMany(TaxiCity::class);
	}
}
