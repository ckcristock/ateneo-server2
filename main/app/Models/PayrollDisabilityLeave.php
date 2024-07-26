<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollDisabilityLeave extends Model
{
    protected $table = 'payroll_disability_leaves';

    protected $fillable = [
        'disability_leave_id',
        'account_plan_id',
        'percentage',
        'company_id',
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
