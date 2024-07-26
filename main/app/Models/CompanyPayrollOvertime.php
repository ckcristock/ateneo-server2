<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPayrollOvertime extends Model
{
    protected $table = 'company_payroll_overtime';

    protected $fillable = [
        'payroll_overtime_id',
        'account_plan_id',
        'company_id',
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(PayrollOvertime::class, 'payroll_overtime_id');
    }

    public function accountPlan()
    {
        return $this->belongsTo(PlanCuentas::class, 'account_plan_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getPercentageAttribute($value)
    {
        return (float) $value;
    }
}
