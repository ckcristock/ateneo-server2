<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPayrollSocialSecurityPerson extends Model
{
    protected $table = 'company_payroll_social_security_person';

    protected $fillable = [
        'payroll_social_security_person_id',
        'account_plan_id',
        'account_setoff',
        'company_id',
        'percentage'
    ];

    public function payroll()
    {
        return $this->belongsTo(PayrollSocialSecurityPerson::class, 'payroll_social_security_person_id');
    }

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
