<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractDepartment extends Model
{
    protected $table = 'contract_department';

    protected $fillable = [
        'contract_id',
        'department_id'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
