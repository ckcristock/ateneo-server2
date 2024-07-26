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
 * Class Rule
 * 
 * @property int $id
 * @property int $operator_id
 * @property int $variable_id
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Operator $operator
 * @property Variable $variable
 * @property Collection|Condition[] $conditions
 * @property Collection|Variable[] $variables
 *
 * @package App\Models
 */
class Rule extends Model
{
	use SoftDeletes;
	protected $table = 'rules';

	protected $casts = [
		'operator_id' => 'int',
		'variable_id' => 'int'
	];

	protected $fillable = [
		'operator_id',
		'variable_id',
		'value'
	];
	//operador mediante el cual se hace la comparacion del valor y la variable 
	public function operator()
	{
		return $this->belongsTo(Operator::class);
	}
	//representa la variable que se muestra si se cumple la regla, es decir la variable hija
	public function variable()
	{
		return $this->belongsTo(Variable::class);
	}
	//trae las condiciones ligadas a la regla.
	public function conditions()
	{
		return $this->belongsToMany(Condition::class, 'rule_condition')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}

	public function variables()
	{
		return $this->belongsToMany(Variable::class, 'variable_rule')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}
}
