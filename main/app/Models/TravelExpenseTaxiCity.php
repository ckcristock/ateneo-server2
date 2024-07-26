<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelExpenseTaxiCity extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'journeys',
        'taxi_city_id',
        'rate',
        'total',
        'travel_expense_id',
    ];

    public function taxiCity()
    {
        return $this->belongsTo(TaxiCity::class);
    }
}
