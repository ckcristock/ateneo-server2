<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoriesPlace extends Model
{
    protected $fillable = [
        'name',
        'nit',
        'verification_digit',
        'city',
        'contract'
    ];
}
