<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableConditionsValues extends Model
{
    use HasFactory;
    protected $table = "variable_conditions_values";

    protected $fillable = [
        "variable_id",
        "type_condition_id",
        "value",
    ];
}
