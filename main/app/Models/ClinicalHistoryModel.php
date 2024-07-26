<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalHistoryModel extends Model
{
    protected $fillable = ['name', 'description', 'type_clinical_history_model_id', 'sub_type_clinical_history_model_id'];

    public function typeClinicalHistoryModel()
    {
        return $this->belongsTo(TypeClinicalHistoryModel::class);
    }
    public function subTypeClinicalHistoryModel()
    {
        return $this->belongsTo(SubTypeClinicalHistoryModel::class);
    }
}
