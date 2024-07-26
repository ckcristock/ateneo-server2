<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCountableSalary extends Model
{
    protected $table = 'company_countable_salary';

    protected $fillable = [
        'countable_salary_id',
        'account_plan_id',
        'account_setoff',
        'company_id',
        'status'
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
