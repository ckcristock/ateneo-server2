<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeverancePaymentPerson extends Model
{
    protected $fillable = [
        'severance_payment_id',
        'person_id',
        'total',
        'company_id',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class)->onlyName();
    }

    public function severancePayment()
    {
        return $this->belongsTo(SeverancePayment::class);
    }
}
