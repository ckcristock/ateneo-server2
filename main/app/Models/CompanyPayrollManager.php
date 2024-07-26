<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPayrollManager extends Model
{
    protected $table = 'company_payroll_manager';

    protected $fillable = [
        'payroll_manager_id',
        'person_id',
        'company_id',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payroll()
    {
        return $this->belongsTo(PayrollManager::class);
    }
}
