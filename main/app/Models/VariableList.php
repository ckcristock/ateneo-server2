<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class VariableList
 * 
 * @property int $id
 * @property int $variable_id
 * @property int $list_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property ListModel $list
 * @property Variable $variable
 *
 * @package App\Models
 */
class VariableList extends Model
{
	use SoftDeletes;
	protected $table = 'variable_list';

	protected $casts = [
		'variable_id' => 'int',
		'list_id' => 'int'
	];

	protected $fillable = [
		'variable_id',
		'list_id'
	];

	public function list()
	{
		return $this->belongsTo(ListModel::class);
	}

	public function variable()
	{
		return $this->belongsTo(Variable::class);
	}
}
