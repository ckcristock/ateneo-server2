<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RrhhActivityType extends Model
{
    protected $fillable = [
        'name',
        'color',
        'state',
        'company_id',
    ];

    public function getNameAttribute()
    {
        return strtoupper($this->attributes['name']);
    }
}
