<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCondition extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'value', 'type'];
    
    public function variableTypes()
    {
        return $this->belongsToMany(VariableType::class, 'variable_types_conditions');
    }

    public function variables()
    {
        return $this->belongsToMany(Variable::class, 'variable_conditions_values', 'type_condition_id', 'variable_id')
            ->withPivot('value');
    }
}
