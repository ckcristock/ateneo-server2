<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
  //  public function clinicalHistoryModel()
  // {
  //     return $this->belongsTo(ClinicalHistoryModel::class);
  // }

  public function variablesFormTemplate()
  {
    return $this->hasMany(VariablesFormTemplate::class);
  }

  //   public function variablesClinicalHistoryModel()
  // {
  //     return $this->hasMany(VariablesClinicalHistoryModel::class);
  // }
}
