<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTerm extends Model
{
    protected $table = 'contract_terms';
    protected $fillable = [
        'id',
        'name',
        'status',
        'conclude',
        'modified',
        'description',
    ];

    public function workContractTypes()
    {
        return $this->belongsToMany(WorkContractType::class)
            ->withTimestamps();
    }

}
