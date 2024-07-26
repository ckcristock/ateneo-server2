<?php

namespace App\Http\Controllers;

use App\Http\Libs\Nomina\Facades\NominaDeducciones;
use App\Http\Libs\Nomina\Facades\NominaIngresos;
use App\Http\Libs\Nomina\Facades\NominaLiquidacion;
use App\Http\Libs\Nomina\Facades\NominaNovedades;
use App\Http\Libs\Nomina\Facades\NominaPago;
use App\Http\Libs\Nomina\Facades\NominaProvisiones;
use App\Http\Libs\Nomina\Facades\NominaRetenciones;
use App\Http\Libs\Nomina\Facades\NominaSeguridad;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Company;
use App\Models\PayrollOvertime;
use App\Models\Person;
use App\Models\PersonPayrollPayment;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use App\Models\PreliquidatedLog;
use App\Models\WorkContract;

class LiquidacionesController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function get($id, $fechaFin = null)
    {
        return NominaLiquidacion::liquidacionFuncionarioWithPerson($id)
            ->until($fechaFin)->calculate()
            ->makeResponse();
    }

    public function getWithVacacionesActuales($id)
    {
        $fechaFin = null;
        $diasAcumulados = 0;
        if (request()->has(['diasAcumulados', 'fechaFin'])) {
            $fechaFin = request('fechaFin');
            $diasAcumulados = request('diasAcumulados');
        }
        return
            NominaLiquidacion::liquidacionFuncionarioWithPerson($id)
            ->until($fechaFin)
            ->withVacacionesActuales($diasAcumulados)
            ->calculate()
            ->makeResponse();
    }

    public function getWithSalarioBase($id)
    {
        $fechaFin = null;
        $salarioBase = 0;

        if (request()->has(['fechaFin', 'salarioBase'])) {
            $fechaFin = request('fechaFin');
            $salarioBase = request('salarioBase');
        }

        return NominaLiquidacion::liquidacionFuncionarioWithPerson($id)->until($fechaFin)->withSalarioBase($salarioBase)->calculate()->makeResponse();
    }

    public function getWithBases($id)
    {
        $fechaRetiro = null;
        $salarioBase = 0;
        $baseVacaciones =  0;
        $basePrima = 0;
        $baseCesantias = 0;

        if (request()->has(['fechaRetiro', 'salarioBase', 'baseVacaciones', 'basePrima', 'baseCesantias'])) {
            $fechaRetiro = request('fechaRetiro');
            $salarioBase = request('salarioBase');
            $baseVacaciones = request('baseVacaciones');
            $baseCesantias = request('baseCesantias');
            $basePrima = request('basePrima');
        }

        return NominaLiquidacion::liquidacionFuncionarioWithPerson($id)->until($fechaRetiro)
            ->withSalarioBase($salarioBase)
            ->withBaseVacaciones($baseVacaciones)
            ->withBaseCesantias($baseCesantias)
            ->withBasePrima($basePrima)
            ->calculate()
            ->makeResponse();
    }

    public function getWithIngresos($id)
    {
        $fechaRetiro = '';
        $ingresos = 0;
        $egresos = 0;

        if (request()->has(['ingresos', 'egresos'])) {
            $fechaRetiro = request('fechaRetiro');
            $ingresos = request('ingresos');
            $egresos = request('egresos');
        }

        return NominaLiquidacion::liquidacionFuncionarioWithPerson($id)
            ->until($fechaRetiro)
            ->withIngresos($ingresos)
            ->withEgresos($egresos)
            ->calculate()
            ->makeResponse();
    }

    public function getPdfLiquidacion()
    {
        $funcionario = null;
        $nombrePdf = null;
        $empresa = Company::find($this->getCompany())['razon_social'];

        if (request()->has('funcionario')) {

            $funcionario = request('funcionario');
            // $nombrePdf = $funcionario['nombres'] . '_' . $funcionario['apellidos'] . '.pdf';
            $nombrePdf =  'liqejemplo.pdf';
            // return view('nomina.liquidacion', compact('funcionario'));
        }
        $funcionario['empresa'] = $empresa;
        return PDF::loadView('nomina.liquidacion', compact('funcionario'))->download($nombrePdf);
    }

    public function getSeguridad($id, $fechaInicio, $fechaFin)
    {

        return NominaSeguridad::seguridadFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getExtrasTotales($id, $fechaInicio, $fechaFin)
    {

        return PayrollOvertime::extrasFuncionarioWithPerson($id)->fromTo($fechaInicio, $fechaFin);
    }

    public function getPagoNeto($persona, $fechaInicio, $fechaFin)
    {
        return NominaPago::pagoFuncionarioWithPerson($persona)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getRetenciones($id, $fechaInicio, $fechaFin)
    {
        return  NominaRetenciones::retencionesFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }

    public function getProvisiones($persona, $fechaInicio, $fechaFin)
    {
        return NominaProvisiones::provisionesFuncionarioWithPerson($persona)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }
    public function getIngresos($id, $fechaInicio, $fechaFin)
    {

        return NominaIngresos::ingresosFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }
    public function getNovedades($id, $fechaInicio, $fechaFin)
    {
        return NominaNovedades::novedadesFuncionarioWithPerson($id)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }
    public function getDeducciones($person, $fechaInicio, $fechaFin)
    {
        return NominaDeducciones::deduccionesFuncionarioWithPerson($person)
            ->fromTo($fechaInicio, $fechaFin)
            ->calculate();
    }


    public function getDiasTrabajados($id, $fechaFin)
    {
        $fecha = explode('-', $fechaFin);

        $ultimoPago = PersonPayrollPayment::where('person_id', $id)->with('payrollPayment')->latest('created_at')->first();

        if ($ultimoPago) {
            $fechaInicio = $ultimoPago->payrollPayment->end_period;
        } else {
            $fechaInicio = WorkContract::where('person_id', $id)->where('liquidated', 1)->latest()->first()->date_of_admission;
        }
        $fechaInicioPeriodo = Carbon::createFromFormat("Y-m-d", $fechaInicio)->format("Y-m-d H:i:s");

        $preliquidated = PreliquidatedLog::where('person_id', $id)->latest()->first();

        $fechaFinPeriodo =  Carbon::createFromFormat("Y-m-d H:i:s", $preliquidated->liquidated_at)->format("Y-m-d H:i:s");

        $fechasNovedades = function ($query) use ($fechaInicioPeriodo, $fechaFinPeriodo) {
            return $query->whereBetween('date_start', [$fechaInicioPeriodo, $fechaFinPeriodo])
                ->whereBetween('date_end', [$fechaInicioPeriodo, $fechaFinPeriodo])
                ->with('disability_leave');
        };
        $funcionario = Person::where('id', $id)
            ->with('contractUltimateLiquidated')
            ->with(['payroll_factors' => $fechasNovedades])
            ->first(['id', 'identifier', 'first_name', 'first_surname', 'image']);
        $funcionario['contractultimate'] = $funcionario['contractUltimateLiquidated']['workContractBT'];

        if (!$funcionario) {
            $funcionariosResponse = [
                'id' => null,
                'identifier' => null,
                'name' => null,
                'surname' => null,
                'image' => null,
                'inicio_periodo' => $fechaInicioPeriodo,
                'fin_periodo' => $fechaFinPeriodo,
                'seguridad_social' => 0,
                'parafiscales' => 0,
                'provisiones' => 0,
                'extras' => 0,
                'ingresos' => 0,
                'retenciones' => 0,
                'salario_neto' => 0,
                'novedades' => [],
                'horas_extras' => 0,
                'valor_ingresos_salariales' => 0,
                'valor_ingresos_no_salariales' => 0,
                'valor_deducciones' => 0
            ];
            return $this->success($funcionariosResponse);
        }

        try {
            $tempSeguridad = $this->getSeguridad($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);
            $seguridad = $tempSeguridad['valor_total_seguridad'];
            $parafiscal = $tempSeguridad['valor_total_parafiscales'];
            $tempExtras = $this->getExtrasTotales($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);
            $extras = $tempExtras['valor_total'];
            $salario = $this->getPagoNeto($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['total_valor_neto'];
            $retencion = $this->getRetenciones($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['valor_total'];
            $provision = $this->getProvisiones($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)->valor_total;
            $temIngresos = $this->getIngresos($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo);

            $funcionariosResponse = [
                'id' => $funcionario->id,
                'identifier' => $funcionario->identifier,
                'name' => $funcionario->first_name,
                'surname' => $funcionario->first_surname,
                'image' => $funcionario->image,
                'inicio_periodo' => $fechaInicioPeriodo,
                'fin_periodo' => $fechaFinPeriodo,
                'seguridad_social' => $seguridad,
                'parafiscales' => $parafiscal,
                'provisiones' => $provision,
                'extras' => $extras,
                'ingresos' => $temIngresos,
                'retenciones' => $retencion,
                'salario_neto' => $salario,
                'horas_extras' => $tempExtras['horas_reportadas'],
                'novedades' => $this->getNovedades($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['novedades'],
                'valor_ingresos_salariales' => ($temIngresos->valor_constitutivos +
                    $this->getNovedades($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)['valor_total'] +
                    $extras),
                'valor_ingresos_no_salariales' => $temIngresos->valor_no_constitutivos,
                'valor_deducciones' => $this->getDeducciones($funcionario, $fechaInicioPeriodo, $fechaFinPeriodo)->valor_total
            ];

            return $this->success($funcionariosResponse);
        } catch (\Throwable $th) {
            //throw $th;
            return [$th->getLine() . ' ' . $th->getFile() . ' ' . $th->getMessage()];
        }
    }
}
