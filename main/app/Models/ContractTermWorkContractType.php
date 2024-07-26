<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractTermWorkContractType extends Model
{

    protected $table = "contract_term_work_contract_type";
    protected $fillable = [
        'contract_term_id',
        'work_contract_type_id',
    ];
}
