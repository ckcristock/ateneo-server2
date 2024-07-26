<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPayrollRisksArl extends Model
{
    protected $table = 'company_payroll_risks_arl';

    protected $fillable = [
        'payroll_risks_arl_id',
        'account_plan_id',
        'account_setoff',
        'company_id',
        'percentage'
    ];

    public function accountPlan()
    {
        return $this->belongsTo(PlanCuentas::class, 'account_plan_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function accountSetoffInfo()
    {
        return $this->belongsTo(PlanCuentas::class, 'account_setoff');
    }

    public function payroll()
    {
        return $this->belongsTo(PayrollRisksArl::class, 'payroll_risks_arl_id');
    }
}
