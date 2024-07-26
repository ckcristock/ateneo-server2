<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Variable
 * 
 * @property int $id
 * @property string $name
 * @property string $size
 * @property bool $required
 * @property int $section_id
 * @property int $variable_type_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Section $section
 * @property VariableType $variable_type
 * @property Collection|Condition[] $conditions
 * @property Collection|Option[] $options
 * @property Collection|Rule[] $rules
 * @property Collection|VariableConditionsValues[] $variable_conditions_values
 * @property Collection|ListModel[] $lists
 *
 * @package App\Models
 */
class Variable extends Model
{
	use SoftDeletes;
	protected $table = 'variables';

	protected $casts = [
		'required' => 'bool',
		'section_id' => 'int',
		'variable_type_id' => 'int'
	];

	protected $fillable = [
		'name',
		'size',
		'required',
		'section_id',
		'variable_type_id',
		'parent'
	];
	//trae la seccion a la que pertenece la variable
	public function section()
	{
		return $this->belongsTo(Section::class);
	}
	//trae el tipo de variable, que a su vez esta ligado a unas condiciones de variable
	public function variable_type()
	{
		return $this->belongsTo(VariableType::class);
	}
	//las condiciones son restricciones que ligan a las variables, por lo tanto la relacion trae la variable que esta condicionada a cierto valor
	public function conditions()
	{
		return $this->hasMany(Condition::class);
	}
	//cuando la variable es de tipo select, si la lista select es nueva las opciones se guardan en esta tabla
	public function options()
	{
		return $this->hasMany(Option::class);
	}
	//una variable puede tener multiples reglas y cada regla lo que va a hacer es permitir mostrar otra variable condicionada
	public function variableRules()
    {
        return $this->hasMany(VariableRule::class);
    }

	public function rules()
	{
		return $this->belongsToMany(Rule::class, 'variable_rule')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}
	//son los valores que se guardan para las condiciones por el tipo de variable
	public function variable_conditions_values()
	{
		return $this->hasMany(VariableConditionsValues::class);
	}
	//son las listas existentes en el sistema como cie10 etc
	public function lists()
	{
		return $this->belongsToMany(ListModel::class, 'variable_list','variable_id','list_id')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}
	//son las condiciones de tipo de variable es decir, por ejemplo en un campo tipo string, seria el length
	public function typeConditions()
    {
        return $this->belongsToMany(TypeCondition::class, 'variable_conditions_values', 'variable_id', 'type_condition_id')
            ->withPivot('value');
    }
}
