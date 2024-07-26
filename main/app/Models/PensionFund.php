<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PensionFund extends Model
{
    protected $table = 'pension_funds';
    protected $fillable = [
        'name',
        'nit',
        'code',
        'status'
    ];

    public function getNameAttribute($value)
    {
        return strtoupper($value);
    }
}
