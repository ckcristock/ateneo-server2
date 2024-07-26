<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RuleCondition
 * 
 * @property int $id
 * @property int $condition_id
 * @property int $rule_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Condition $condition
 * @property Rule $rule
 *
 * @package App\Models
 */
class RuleCondition extends Model
{
	use SoftDeletes;
	protected $table = 'rule_condition';

	protected $casts = [
		'condition_id' => 'int',
		'rule_id' => 'int'
	];

	protected $fillable = [
		'condition_id',
		'rule_id'
	];

	public function condition()
	{
		return $this->belongsTo(Condition::class);
	}

	public function rule()
	{
		return $this->belongsTo(Rule::class);
	}
}
