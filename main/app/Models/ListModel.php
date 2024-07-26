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
 * Class List
 * 
 * @property int $id
 * @property string $name
 * @property string $endpoint
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property Collection|Option[] $options
 * @property Collection|Variable[] $variables
 *
 * @package App\Models
 */
class ListModel extends Model
{
	use SoftDeletes;
	protected $table = 'lists';

	protected $fillable = [
		'name',
		'endpoint'
	];

	public function options()
	{
		return $this->hasMany(Option::class, 'list_id');
	}

	public function variables()
	{
		return $this->belongsToMany(Variable::class, 'variable_list')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}
}
