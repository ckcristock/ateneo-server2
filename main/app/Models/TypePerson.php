<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePerson extends Model
{
    protected $table = 'type_persons';
    protected $fillable = [
        "name",
        "description",
    ];
}
