<?php

namespace App\Http\Libs\Nomina\Facades;

use App\Models\Funcionario;
use App\Models\Empresa;

use App\Http\Libs\Nomina\Calculos\CalculoRetenciones;
use App\Http\Libs\Nomina\PeriodoPago;
use App\Models\Company;
use App\Models\Person;

class NominaRetenciones extends PeriodoPago
{
    /**
     * Funcionario al cual se le calculan las retenciones
     *
     * @var  App\Models\Person
     */
    protected static Person $funcionario;

    /**
     * Instancia de la clase CalculoRetenciones
     *
     * @var App\Http\Libs\Nomina\Calculos\CalculoRetenciones;
     */
    protected $calculoRetenciones;

    /**
     * Facade NominaNoveadades
     *
     * @var NominaNovedades;
     */
    protected $facadeNovedades;

    /**
     * Facade NominaSalario
     *
     * @var NominaSalario;
     */
    protected $facadeSalario;

    /**
     * Facade NominaExtras
     *
     * @var NominaExtras;
     */
    protected $facadeExtras;

    /**
     * Instancia de la clase CalculoIngresos
     *
     * @var NominaIngresos;
     */
    protected $facadeIngresos;

    /**
     * Fecha de inicio del periodo de pago
     *
     * @var string
     */
    protected $fechaInicio;

    /**
     * Fecha de fin del periodo de pago
     *
     * @var string
     */
    protected $fechaFin;

    /**
     * Settea la propiedad funcionario filtrando al funcionario que se pase por el parámetro $id,
     * retorna una nueva instancia de la clase
     *
     * @param \App\Models\Person $person

     */
    public static function retencionesFuncionarioWithPerson($persona)
    {

        //self::$funcionario = Person::with('contractultimate')->findOrFail($id);
        self::$funcionario = $persona;
        return new self;
    }


    public function fromTo($fechaInicio, $fechaFin)
    {


        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;

        $this->facadeSalario = NominaSalario::salarioFuncionarioWithPerson(self::$funcionario)->fromTo($this->fechaInicio, $this->fechaFin)->calculate();

        $this->facadeExtras = NominaExtras::extrasFuncionarioWithPerson(self::$funcionario)->fromTo($this->fechaInicio, $this->fechaFin);
        $this->facadeNovedades = NominaNovedades::novedadesFuncionarioWithPerson(self::$funcionario)->fromTo($this->fechaInicio, $this->fechaFin)->calculate();


        $this->facadeIngresos = NominaIngresos::ingresosFuncionarioWithPerson(self::$funcionario)
            ->fromTo($this->fechaInicio, $this->fechaFin)
            ->calculate();

        return $this;
    }

    public function withParams($salario, $extras, $novedades, $ingresos, $fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->facadeSalario = $salario;
        $this->facadeExtras = $extras;
        $this->facadeNovedades = $novedades;

        $this->facadeIngresos = $ingresos;

        return $this;
    }


    public function calculate()
    {

        $this->calculoRetenciones = new CalculoRetenciones(
            Company::find(Person::find(Auth()->user()->person_id)->company_worked_id)['base_salary'],
            self::$funcionario->contractultimate->salary
        );


        $this->calculoRetenciones->registrar('Salario', $this->facadeSalario['salary']);

        $this->calculoRetenciones->registrar('Horas extras', $this->facadeExtras['valor_total']);

        $this->calculoRetenciones->registrarNovedades($this->facadeNovedades['novedades_totales']);

        $this->calculoRetenciones->registrarIngresos($this->facadeIngresos['constitutivos']);

        $this->calculoRetenciones->calcularIbcSeguridad();


        $this->calculoRetenciones

            ->calculoIBCSalud()
            ->calculoIBCPension()
            ->calculoIBCFondoSolidaridad()
            ->calculoIBCFondoSubsistencia()
            ->calculoIBCRetencionFuente();



        return $this->calculoRetenciones->crearColeccion();
    }
}
