<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkContractFinishConditions extends Model
{
    protected $fillable = [
        'renewed',
        'contract_id',
        'work_contract_type_id',
        'contract_term_id',
        'group_id',
        'dependency_id',
        'position_id',
        'company_id',
        'liquidated',
        'person_id',
        'salary',
        'turn_type',
        'fixed_turn_id',
        'rotating_turn_id',
        'date_of_admission',
        'date_end',
        'old_date_end'
    ];

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }
}
