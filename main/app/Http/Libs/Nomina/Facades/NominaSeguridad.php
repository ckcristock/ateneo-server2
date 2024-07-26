<?php

namespace App\Http\Libs\Nomina\Facades;

use App\Http\Libs\Nomina\Calculos\CalculoSeguridad;
use App\Http\Libs\Nomina\PeriodoPago;
use App\Models\Company;
use App\Models\Person;

class NominaSeguridad extends PeriodoPago
{
    /**
     * Funcionario al cual se le calcula el salario
     *
     * @var  App\Models\Person
     */
    protected static Person $funcionario;

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
     * Facade NominaRetenciones
     *
     * @var NominaRetenciones
     */
    protected $facadeRetenciones;

    /**
     * Facade NominaNovedades
     *
     * @var NominaNovedades
     */
    protected $facadeNovedades;

    /**
     * Instancia CalculoSeguridad
     *
     * @var CalculoSeguridad
     */
    protected $calculoSeguridad;
    protected static $empresa;

    /**
     * Settea la propiedad funcionario filtrando al funcionario que se pase por el parámetro $id,
     * retorna una nueva instancia de la clase
     *
     * @param App\Models\Person $persona
          */
    public static function seguridadFuncionarioWithPerson($persona)
    {
        //self::$funcionario = Person::with('contractultimate')->findOrFail($id);
        //self::$funcionario = $persona;
        //dd($persona);
        if (gettype($persona)==='string' || gettype($persona)==='integer') {
            self::$funcionario = Person::with('contractultimate')->findOrFail($persona);
        }else {
            self::$funcionario = $persona;
        }
        //$this->funcionario =$persona;
        self::$empresa = Company::find(Person::find(Auth()->user()->person_id)->company_worked_id);
        return new self;
    }

    public function fromTo($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        //dd($this->fechaInicio);
        $this->fechaFin = $fechaFin;
        //dd($this->fechaFin);

        $this->facadeRetenciones = NominaRetenciones::retencionesFuncionarioWithPerson(self::$funcionario)
            ->fromTo($this->fechaInicio, $this->fechaFin)
            ->calculate();

        $this->facadeNovedades = NominaNovedades::novedadesFuncionarioWithPerson(self::$funcionario)
            ->fromTo($this->fechaInicio, $this->fechaFin)
            ->calculate();


        return $this;
    }

    public function withParams($retenciones, $novedades, $fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;

        $this->facadeRetenciones = $retenciones;
        $this->facadeNovedades = $novedades;

        return $this;
    }

    public function calculate()
    {
        $salarioMinimo = self::$empresa['base_salary'];

        $this->calculoSeguridad = new CalculoSeguridad(
            $this->facadeRetenciones['IBC_seguridad'],
            $this->facadeRetenciones['retenciones']['Salario'],
            self::$funcionario->contractultimate->salary,
            $this->facadeRetenciones['retenciones']['Horas extras'],
            $this->facadeRetenciones['retenciones']['Ingresos'],
            $this->facadeNovedades['novedades_totales']['Vacaciones'] ?? 0
        );

        $this->calculoSeguridad->calcularIbcRiesgos($salarioMinimo)->calcularIbcParafiscales();

        $this->calculoSeguridad->calcularPension()->calcularSalud(self::$empresa['law_1607'], $salarioMinimo)
            ->calcularRiesgos(self::$funcionario);

        $this->calculoSeguridad
            ->calcularSena(self::$empresa['law_1607'], $salarioMinimo)
            ->calcularIcbf(self::$empresa['law_1607'], $salarioMinimo)
            ->calcularCajaCompensacion();

        $this->calculoSeguridad
            ->calcularTotalSeguridadSocial()
            ->calcularTotalParafiscales();

        $this->calculoSeguridad->calcularTotal();

        return $this->calculoSeguridad->crearColeccion();
    }
}
