<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VariablesFormTemplate extends Model
{
  public function valuesForSelect()
  {
    return $this->hasMany(ValuesFormForSelect::class);
  }


  protected $appends = ['parents'];

  public function getParentsAttribute()
  {
    return DB::table('parents_for_fields')
      ->select('parent_id', 'name as valueDependence')
      ->join('fields_dependences as fd', 'fd.parents_for_fields_id', 'parents_for_fields.id')
      ->where('variables_clinical_history_model_id', $this->id)
      ->get();
  }

}
