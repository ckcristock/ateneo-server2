<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalResponsibility extends Model
{
    protected $fillable = [
        'code',
        'name',
        'state'
    ];
}
