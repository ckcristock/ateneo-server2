<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeveranceInterestPaymentPerson extends Model
{
    protected $fillable = [
        'severance_interest_payment_id',
        'person_id',
        'total',
        'company_id',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class)->onlyName();
    }
    public function severanceInterestPayment()
    {
        return $this->belongsTo(SeveranceInterestPayment::class);
    }
}
