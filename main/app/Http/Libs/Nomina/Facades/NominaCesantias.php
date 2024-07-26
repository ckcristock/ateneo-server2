<?php

namespace App\Http\Libs\Nomina\Facades;

use App\Http\Libs\Nomina\Calculos\CalculoCesantias;
use App\Http\Libs\Nomina\PeriodoPago;
use App\Models\Company;
use App\Models\Person;

class NominaCesantias extends PeriodoPago
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
     * Instancia CalculoSeguridad
     *
     * @var CalculoCesantias
     */
    protected $calculoCesantias;
    protected static $empresa;

    /**
     * Settea la propiedad funcionario filtrando al funcionario que se pase por el parámetro $id,
     * retorna una nueva instancia de la clase
     *
     * @param App\Models\Person $persona
          */
    public static function cesantiaFuncionarioWithPerson($persona)
    {

        if (gettype($persona)==='string' || gettype($persona)==='integer') {
            self::$funcionario = Person::with('contractultimate')->findOrFail($persona);
        } else {
            self::$funcionario = $persona;
        }
        self::$empresa = Company::find(Person::find(Auth()->user()->person_id)->company_worked_id);
        return new self;
    }

    public function fromTo($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function calculate()
    {
        $salarioMinimo = self::$empresa['base_salary'];
        //dd(self::$funcionario->toArray());
        $this->calculoCesantias = new CalculoCesantias(self::$funcionario);
        $this->calculoCesantias->calcularCesania();
        return $this->calculoCesantias->crearColeccion();
    }
}
