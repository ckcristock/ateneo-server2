<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkContract extends Model
{
    protected $fillable = [
        'position_id',
        'company_id',
        'liquidated',
        'person_id',
        'salary',
        'turn_type',
        'fixed_turn_id',
        'date_of_admission',
        'work_contract_type_id',
        'contract_term_id',
        'date_end',
        'old_date_end',
        'rotating_turn_id',
        'transport_assistance'
    ];

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }

    public function position()
    {
        return $this->belongsTo(Position::class)->with('dependency');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function people()
    {
        return $this->belongsTo(Person::class);
    }

    public function work_contract_type()
    {
        return $this->belongsTo(WorkContractType::class);
    }

    public function contract_term()
    {
        return $this->belongsTo(ContractTerm::class);
    }


    /**
     * El contrato pertenece a un funcionario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the user that owns the WorkContract
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fixedTurn()
    {
        return $this->belongsTo(FixedTurn::class)->with('horariosTurnoFijo');
    }
    /**
     * Get the user that owns the WorkContract
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rotatingTurn()
    {
        return $this->belongsTo(RotatingTurn::class);
    }

    public function rotatingTurnWithDiaries()
    {
        return $this->belongsTo(RotatingTurn::class)->with('diariosTurnoRotativo');
    }

    public function bonifications()
    {
        return $this->hasMany(Bonifications::class);
    }

}
