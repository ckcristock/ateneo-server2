<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'country_id',
        'dian_code',
        'dane_code',
    ];

    public function municipalities()
    {
        return $this->hasMany(Municipality::class)->orderBy('name');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
