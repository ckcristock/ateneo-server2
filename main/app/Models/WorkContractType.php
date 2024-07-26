<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkContractType extends Model
{
    protected $fillable = [
        'id',
        'name',
        'status',
        'conclude',
        'modified',
        'description',
        'template'
    ];

    protected $table = 'work_contract_types';

    public function contractTerms()
    {
        return $this->belongsToMany(ContractTerm::class)->withTimestamps();
    }
}
