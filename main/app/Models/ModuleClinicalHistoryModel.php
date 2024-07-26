<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleClinicalHistoryModel extends Model
{

    protected $fillable = ['name', 'clinical_history_model_id'];


    public function clinicalHistoryModel()
    {
        return $this->belongsTo(ClinicalHistoryModel::class);
    }

    public function variablesClinicalHistoryModel()
    {
        return $this->hasMany(VariablesClinicalHistoryModel::class);
    }
}
