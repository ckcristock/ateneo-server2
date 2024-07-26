<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryRotatingTurnHour extends Model
{
    protected $fillable = [
        'rotating_turn_hour_id',
        'person_id',
        'batch',
        'action',
        'company_id',
    ];

    public function rotating_turn_hour()
    {
        return $this->belongsTo(RotatingTurnHour::class)->with('person', 'turnoRotativo');
    }

    public function person()
    {
        return $this->belongsTo(Person::class)->fullName();
    }
}
