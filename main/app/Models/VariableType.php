<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableType extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "value",
        "primary",
    ];

    public function conditions()
    {
        return $this->belongsToMany(TypeCondition::class, 'variable_types_conditions');
    }
}
