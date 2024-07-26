<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegimenType extends Model
{
    protected $fillable = [
        'name',
        'state',
        'short',
        'code'
    ];
}
