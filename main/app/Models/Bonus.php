<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'description',
        'total_bonuses',
        'total_employees',
        'period',
        'payment_date',
        'status',
        'payer',
        'payer_identifier',
        'payer_fullname',
        'observations',
        'company_id',
    ];

    public function bonusPerson()
    {
        return $this->hasMany(BonusPerson::class)->with('person');
    }

    public function personPayer()
    {
        return $this->belongsTo(Person::class, 'payer', 'id');
    }

}
