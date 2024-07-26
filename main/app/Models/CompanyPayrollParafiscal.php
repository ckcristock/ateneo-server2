<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPayrollParafiscal extends Model
{
    protected $table = 'company_payroll_parafiscal';

    protected $fillable = [
        'payroll_parafiscal_id',
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
        return $this->belongsTo(PayrollParafiscal::class, 'payroll_parafiscal_id');
    }
}
