<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PayrollOvertime;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PayrollOvertimeController extends Controller
{

    private function getCompany()
    {
        return Person::find(auth()->user()->person_id)->company_worked_id;
    }

    public function horasExtrasPorcentajes2()
    {
        return PayrollOvertime::pluck('percentage', 'prefix');
    }

    public function horasExtrasPorcentajes()
    {
        $company = Company::with('payrollOvertime.payroll')->find($this->getCompany());
        $data = new Collection();
        foreach ($company->payrollOvertime as $payrollOvertime) {
            $percentage = $payrollOvertime->percentage;
            $prefix = optional($payrollOvertime->payroll)->prefix;
            $data[$prefix] = $percentage;
        }
        return $data;
    }

}
