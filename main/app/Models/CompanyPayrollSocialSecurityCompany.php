<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPayrollSocialSecurityCompany extends Model
{
    protected $table = 'company_payroll_social_security_company';

    protected $fillable = [
        'payroll_social_security_company_id',
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

}
