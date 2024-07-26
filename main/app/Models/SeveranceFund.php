<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeveranceFund extends Model
{
    protected $fillable = [
        'name',
        'code',
        'nit',
        'status'
    ];

    public function getNameAttribute($value)
    {
        return strtoupper($value);
    }

    public function personAfiliates()
    {
        return $this->hasMany(Person::class, 'severance_fund_id', 'id');
    }
}
