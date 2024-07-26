<?php

namespace App\Http\Libs\Nomina\Facades;

use App\Models\NominaHorasExtras;
use App\Http\Libs\Nomina\Calculos\CalculoExtra;
use App\Models\ReporteExtras;
use App\Http\Libs\Nomina\PeriodoPago;
use App\Models\ExtraHourReport;
use App\Models\PayrollOvertime;
use App\Models\Person;

class NominaExtras extends PeriodoPago
{
    protected static Person $funcionario;

    /**
     * Settea la propiedad funcionario que se pasa por el parámetro $person,
     * retorna una nueva instancia de la clase
     *
     * @param App\Models\Person $persona

     */
    public static function extrasFuncionarioWithPerson($persona)
    {
        self::$funcionario = $persona;
        return new self;
    }

    /**
     * Obtiene los prefijos de la tabla nomina_horas_extras, filtra los reportes de horas extras que tenga
     * el funcionario entre el parámetro fecha de inicio y el de fecha de fin , obtiene su salario base
     * y realiza el cálculo correspondiente con esos tres valores (Ver la clase Cálculo extras)
     *
     * @param integer $fechaInicio
     * @param integer $fechaFin
     * @return Illuminate\Support\Facades\Collection
     */
    public function fromTo($fechaInicio, $fechaFin)
    {
        //!Se comentan ciertas cosas que parece que no afectan, PARECE
        $prefijos = PayrollOvertime::get(['prefix'])->keyBy('prefix')->keys();
        $reporteExtras =  ExtraHourReport::where('person_id', self::$funcionario)->whereBetween('date', [$fechaInicio, $fechaFin]);
        //$salarioPartial = Person::with('contractultimate')->where('id', self::$funcionario->id)->firstOrFail();
        //dd(self::$funcionario);
        $salarioPartial = self::$funcionario->contractultimate;
        //$salario = $salarioPartial->contractultimate->salary;
        $salario = $salarioPartial->salary;

        $calculoExtras = new CalculoExtra($prefijos, $reporteExtras, $salario);
        $calculoExtras->calcularCantidadHoras();

        $calculoExtras->setHorasReportadas($calculoExtras->getCantidadHoras());
        //$calculoExtras->getPrefijos() es lo mismo que $prefijos
        $calculoExtras->setPorcentajes(
            PayrollOvertime::enviarPorcentajes($calculoExtras->getPrefijos())
        );

        $calculoExtras->calcularTotalHoras();
        $calculoExtras->calcularValorTotalHoras();
        return $calculoExtras->crearColeccion();
    }
}
