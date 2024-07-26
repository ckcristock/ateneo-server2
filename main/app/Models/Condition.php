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
 * Class Condition
 * 
 * @property int $id
 * @property int $variable_id
 * @property int $operator_id
 * @property string $value
 * @property string $logical_operator
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Operator $operator
 * @property Variable $variable
 * @property Collection|Rule[] $rules
 *
 * @package App\Models
 */
class Condition extends Model
{
	use SoftDeletes;
	protected $table = 'conditions';

	protected $casts = [
		'variable_id' => 'int',
		'operator_id' => 'int'
	];

	protected $fillable = [
		'variable_id',
		'operator_id',
		'value',
		'logical_operator'
	];

	public function operator()
	{
		return $this->belongsTo(Operator::class);
	}

	public function variable()
	{
		return $this->belongsTo(Variable::class);
	}

	public function rules()
	{
		return $this->belongsToMany(Rule::class, 'rule_condition')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}
}
