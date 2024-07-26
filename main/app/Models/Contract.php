<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $fillable = [
        "id",
        "name",
        "code",
        "number",
        "administrator_id",
        "contract_type",
        "payment_method_id",
        "payment_methods_contracts_id",
        "benefits_plans_id",
        "benefits_plan_id",
        "start_date",
        "end_date",
        "price",
        "price_list_id",
        "variation",
        "regimen_id",
        "company_id"
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }
    /* public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    } */

    public function departments()
    {
        return $this->hasMany(ContractDepartment::class, 'contract_id', 'id')->with('department');
    }

    public function departments_(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function type_service(): BelongsToMany
    {
        return $this->belongsToMany(TypeService::class);
    }

    public function regimentypes(): BelongsToMany
    {
        return $this->belongsToMany(RegimenType::class);
    }

    public function municipalities(): BelongsToMany
    {
        return $this->belongsToMany(Municipality::class);
    }
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class);
    }
    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class);
    }
    public function technicNotes(): HasMany
    {
        return $this->hasMany(TechnicNote::class);
    }
}
