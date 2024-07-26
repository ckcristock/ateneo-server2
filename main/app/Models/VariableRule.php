<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class VariableRule
 * 
 * @property int $id
 * @property int $variable_id
 * @property int $rule_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Rule $rule
 * @property Variable $variable
 *
 * @package App\Models
 */
class VariableRule extends Model
{
	use SoftDeletes;
	protected $table = 'variable_rule';

	protected $casts = [
		'variable_id' => 'int',
		'rule_id' => 'int'
	];

	protected $fillable = [
		'variable_id',
		'rule_id'
	];

	public function rule()
	{
		return $this->belongsTo(Rule::class);
	}

	public function variable()
	{
		return $this->belongsTo(Variable::class);
	}
}
