<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusPerson extends Model
{
    protected $fillable = [
        'bonus_id',
        'person_id',
        'identifier',
        'fullname',
        'worked_days',
        'amount',
        'lapse',
        'average_amount',
        'payment_date'
    ];

    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
