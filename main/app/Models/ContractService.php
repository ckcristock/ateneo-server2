<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractService extends Model
{
    protected $table = 'contract_services';

    protected $fillable = [
        'type_service_id',
        'contract_id'
    ];
}
