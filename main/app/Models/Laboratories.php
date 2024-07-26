<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratories extends Model
{
    protected $fillable = [
        'patient',
        'date',
        'contract_id',
        'professional_id',
        'cie10_id',
        'motivo_id',
        'observations',
        'laboratory_id',
        'ips_id',
        'specialty_id',
        'hour',
        'status',
        'status_tube',
        'file_order',
        'file_document',
        'file_consentimiento',
    ];
    public function cup()
    {
        return $this->hasMany(CupLaboratory::class, 'id_laboratory', 'id')->with('cup');
    }
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient', 'id')->with('municipality')->with('eps');
    }
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id')->with('company');
    }
    public function place()
    {
        return $this->belongsTo(LaboratoriesPlace::class, 'laboratory_id', 'id');
    }
    public function professional()
    {
        return $this->belongsTo(Person::class, 'professional_id', 'id');
    }
    public function cie10()
    {
        return $this->belongsTo(Cie10::class, 'cie10_id', 'id');
    }
    public function motivo()
    {
        return $this->belongsTo(CausalAnulacion::class, 'motivo_id', 'Id_Causal_Anulacion');
    }
}
