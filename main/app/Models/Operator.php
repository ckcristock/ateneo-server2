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
 * Class Operator
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Collection|Condition[] $conditions
 * @property Collection|Rule[] $rules
 *
 * @package App\Models
 */
class Operator extends Model
{
	use SoftDeletes;
	protected $table = 'operators';

	protected $fillable = [
		'name'
	];

	public function conditions()
	{
		return $this->hasMany(Condition::class);
	}

	public function rules()
	{
		return $this->hasMany(Rule::class);
	}
}
