<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRisksArl extends Model
{
    protected $table = 'payroll_risks_arls';

    protected $fillable = [
        'prefix',
        'concept'
    ];
    public function accounts()
    {
        return $this->hasOne(CompanyPayrollRisksArl::class)
            ->with([
                'accountPlan' => function ($query) {
                    $query->selectRaw('Id_Plan_Cuentas, Id_Plan_Cuentas as value, Codigo_Niif, Nombre_Niif as text');
                },
                'accountSetoffInfo' => function ($query) {
                    $query->selectRaw('Id_Plan_Cuentas, Id_Plan_Cuentas as value, Codigo_Niif, Nombre_Niif as text');
                }
            ])
            ->where('company_id', Person::find(Auth()->user()->person_id)->company_worked_id);
    }

    public function companyPayrollRisksArl()
    {
        return $this->hasMany(CompanyPayrollRisksArl::class);
    }
}
