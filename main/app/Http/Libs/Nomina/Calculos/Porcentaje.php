<?php

namespace App\Http\Libs\Nomina\Calculos;

use App\Models\NominaSeguridadSocialEmpresa;
use App\Models\NominaRiesgosArl;
use App\Models\Funcionario;
use App\Models\NominaParafiscales;
use App\Models\NominaSeguridadSocialFuncionario;
use App\Models\PayrollParafiscal;
use App\Models\PayrollRisksArl;
use App\Models\PayrollSocialSecurityCompany;
use App\Models\PayrollSocialSecurityPerson;
use App\Models\Person;

trait Porcentaje
{

    public function porcentajePension()
    {
        return optional(optional(PayrollSocialSecurityCompany::with('accounts')
            ->where('prefix', '=', 'pension')
            ->first())
            ->accounts)
            ->percentage;
    }

    public function porcentajeSalud()
    {
        return optional(optional(PayrollSocialSecurityCompany::with('accounts')
            ->where('prefix', '=', 'salud')
            ->first())
            ->accounts)
            ->percentage;
    }

    public function porcentajeRiesgosArl(Person $funcionario)
    {
        if (isset($funcionario->payroll_risks_arl_id)) {
            return optional(optional(PayrollRisksArl::with('accounts')
                ->where('id', $funcionario->payroll_risks_arl_id)
                ->first())
                ->accounts)
                ->percentage;
        }
    }

    public function porcentajeSena()
    {
        return optional(optional(PayrollParafiscal::with('accounts')
            ->where('prefix', '=', 'sena')
            ->first())
            ->accounts)
            ->percentage;
    }

    public function porcentajeIcbf()
    {
        return optional(optional(PayrollParafiscal::with('accounts')
            ->where('prefix', '=', 'icbf')
            ->first())
            ->accounts)
            ->percentage;
    }

    public function porcentajeCajaCompensacion()
    {
        return optional(optional(PayrollParafiscal::with('accounts')
            ->where('prefix', '=', 'caja_compensacion')
            ->first())
            ->accounts)
            ->percentage;
    }

    public function porcentajePensionFunc()
    {
        return optional(optional(PayrollSocialSecurityPerson::with('accounts')
            ->where('prefix', '=', 'pension')
            ->first())
            ->accounts)
            ->percentage;
    }

    public function porcentajeSaludFunc()
    {
        return optional(optional(PayrollSocialSecurityPerson::with('accounts')
            ->where('prefix', '=', 'salud')
            ->first())
            ->accounts)
            ->percentage;
    }

    public function porcentajeFondoSolidaridad()
    {
        return optional(optional(PayrollSocialSecurityPerson::with('accounts')
            ->where('prefix', '=', 'fondo_solidaridad')
            ->first())
            ->accounts)
            ->percentage;
    }

    public function porcentajesFondoSubsistencia($tipo)
    {

        return optional(optional(PayrollSocialSecurityPerson::with('accounts')
            ->where('prefix', '=', $tipo)
            ->first())
            ->accounts)
            ->percentage;
    }
}
