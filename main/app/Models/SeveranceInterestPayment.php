<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeveranceInterestPayment extends Model
{
    protected $fillable = [
        'year',
        'total',
        'total_employees',
        'user_id',
        'type',
        'company_id'
    ];

    public function user()
    {
        return $this->belongsTo(Usuario::class)->with('personName')->select('person_id', 'id');
    }

    public function people()
    {
        return $this->hasMany(SeveranceInterestPaymentPerson::class)->with('person');
    }
}
