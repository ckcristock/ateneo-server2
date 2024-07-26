<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCountableLiquidation extends Model
{
    protected $table = 'company_countable_liquidation';

    protected $fillable = [
        'countable_liquidation_id',
        'account_plan_id',
        'company_id',
        'status',
    ];

    public function accountPlan()
    {
        return $this->belongsTo(PlanCuentas::class, 'account_plan_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
