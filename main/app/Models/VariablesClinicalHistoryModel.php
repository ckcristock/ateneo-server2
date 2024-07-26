<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VariablesClinicalHistoryModel extends Model
{
  public function valuesForSelect()
  {
    return $this->hasMany(ValuesForSelect::class);
  }


  protected $appends = ['parents'];

  public function getParentsAttribute()
  {
    return DB::table('parents_for_fields')
      //   ->join('variables_clinical_history_models as vc', 'vc.id', 'parents_for_fields.parent_id')
      ->select('parent_id', 'name as valueDependence')
      ->join('fields_dependences as fd', 'fd.parents_for_fields_id', 'parents_for_fields.id')
      ->where('variables_clinical_history_model_id', $this->id)
      ->get();
  }

}
