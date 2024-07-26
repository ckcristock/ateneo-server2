<?php

namespace App\Http\Libs\Nomina\Facades;

use App\Http\Libs\Nomina\Calculos\CalculoDeducciones;
use App\Http\Libs\Nomina\PeriodoPago;
use App\Models\Deduction;
use App\Models\Loan;

class NominaDeducciones extends PeriodoPago
{
    /**
     * Funcionario al cual se le calculan las retenciones
     *
     * @var  App\Models\Person
     */
    protected static $funcionario;

    /**
     * Instancia de la clase CalculoDeducciones
     *
     * @var App\Http\Libs\Nomina\Calculos\CalculoDeducciones
     */
    protected $calculoDeducciones;

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
     * Settea la propiedad funcionario que se pasa por el parámetro $person,
     * retorna una nueva instancia de la clase
     *
     * @param App\Models\Person $persona

     */
    public static function deduccionesFuncionarioWithPerson($persona)
    {
        self::$funcionario = $persona;
        return new self;
    }

    public function fromTo($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->calculoDeducciones = new CalculoDeducciones(
            Deduction::periodo(self::$funcionario, $this->fechaInicio, $this->fechaFin)
                ->concat((Loan::obtener(self::$funcionario, $this->fechaInicio, $this->fechaFin)))
        );
        return $this;
    }

    public function calculate()
    {

        $this->calculoDeducciones->calcularTotalDeducciones();

        return $this->calculoDeducciones->crearColeccion();
    }
}
