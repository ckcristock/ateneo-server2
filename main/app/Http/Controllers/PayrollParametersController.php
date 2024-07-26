<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Person;

class PayrollParametersController extends Controller
{
    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function porcentajesSeguridadRiesgos($id)
    {
        $companyId = $this->getCompany();
        $funcionario = Person::with('contractultimate')->findOrFail($id);
        $salarioMinimo = Company::find($this->getCompany())['base_salary'];

        // Consulta común para obtener los porcentajes de seguridad social
        $seguridad = Company::with('payrollSocialSecurityPerson.payroll')
            ->find($companyId)
            ->payrollSocialSecurityPerson
            ->mapWithKeys(function ($payrollSocialSecurityPerson) {
                return [$payrollSocialSecurityPerson->payroll->prefix => $payrollSocialSecurityPerson->percentage];
            });

        // Consulta común para obtener los porcentajes de los parafiscales
        $parafiscales = Company::with('payrollParafiscal.payroll')
            ->find($companyId)
            ->payrollParafiscal
            ->mapWithKeys(function ($payrollParafiscal) {
                return [$payrollParafiscal->payroll->prefix => $payrollParafiscal->percentage];
            });
        $empresa = Company::where('id', $companyId)->first(['law_1607']);
        // Condición para ajustar los porcentajes si se cumple cierta condición
        if ($empresa && $empresa->law_1607 && $funcionario->contractultimate->salary < ($salarioMinimo * 10)) {
            $seguridad['salud'] = 0;
            $parafiscales['sena'] = 0;
            $parafiscales['icbf'] = 0;
        }

        // Consulta para obtener los porcentajes de riesgos laborales específicos para este funcionario
        $riesgos = Person::with('payrollRiskArl.companyPayrollRisksArl')
            ->whereHas('payrollRiskArl.companyPayrollRisksArl', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->findOrFail($id)
            ->payrollRiskArl
            ->companyPayrollRisksArl
            ->pluck('percentage')
            ->first();

        return $seguridad->merge(['riesgos' => $riesgos])->merge($parafiscales);
    }

}
