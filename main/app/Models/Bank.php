<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';
    protected $fillable = [
        'name',
        'code',
        'nit',
        'status',
    ];

    public function getNameAttribute($value)
    {
        return strtoupper($value);
    }
}
