<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dotation extends Model
{

    protected $fillable = [
        'dispatched_at',
        'person_id',
        'user_id',
        'description',
        'cost',
        'state',
        'type',
        'delivery_code',
        'delivery_state',
        'company_id'
    ];

    public function dotation_products()
    {
        return $this->hasMany(DotationProduct::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class)->fullName();
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class)->with('person');
    }


    public function dotationProducts()
    {
        return $this->hasMany(DotationProduct::class, 'dotation_id');
    }
    
}
