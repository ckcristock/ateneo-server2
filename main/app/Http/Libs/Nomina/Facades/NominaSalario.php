<?php

namespace App\Http\Libs\Nomina\Facades;

use App\Models\Funcionario;
use App\Models\Empresa;
use App\Models\Novedad;

use App\Http\Libs\Nomina\Calculos\CalculoSalario;
use App\Http\Libs\Nomina\Calculos\CalculoNovedades;
use App\Http\Libs\Nomina\PeriodoPago;
use App\Models\Company;
use App\Models\PayrollFactor;
use App\Models\Person;

class NominaSalario extends PeriodoPago
{
    /**
     * Funcionario al cual se le calcula el salario
     *
     * @var  App\Models\Person
     */
    protected static $funcionario;

    static $auxilioTransporte;

    /**
     * Instancia de la clase CalculoSalario
     *
     * @var App\Http\Libs\Nomina\Calculos\CalculoSalario;
     */
    protected $calculoSalario;

    /**
     * Instancia de la clase CalculoNovedades
     *
     * @var App\Http\Libs\Nomina\Calculos\CalculoNovedades;
     */
    protected $calculoNovedades;

    /**
     * Instancia de la clase CalculoNovedades
     *
     * @var App\Http\Libs\Nomina\Calculos\CalculoNovedades;
     */
    protected $calculoVacaciones;

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
     * @param integer $id

     */
     public function __construct(){
        $this->getAuxilioTransporte();
    }


    public static function salarioFuncionarioWithPerson($persona)
    {
        /*$contratos = function ($q) {
            $q->where('liquidated', 0)->orderBy('id', 'Desc')->take(1);
        };
        self::$funcionario = Person::with('contractultimate')->with(['work_contracts' => $contratos])->findOrFail($id);*/

        self::$funcionario = $persona;
        return new self;
    }


    public static function getAuxilioTransporte()
    {
        self::$auxilioTransporte =  Company::find(Person::find(Auth()->user()->person_id)->company_worked_id)['transportation_assistance'];
    }

    public function fromTo($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->verifiyNovedadesFromTo($this->fechaInicio, $this->fechaFin);

        $this->calculoSalario = new CalculoSalario(
            self::$funcionario->contractultimate->salary,
            $this->calculoNovedades->getDias(),
            $this->fechaInicio,
            $this->fechaFin,
            true
        );


        //dd($this->calculoSalario);

        return $this;
    }

    public function verifiyNovedadesFromTo($fechaInicio, $fechaFin)
    {
        //*Aqui: obtiene la consulta de las novedades /
        $salary = self::$funcionario->contractultimate->salary;
        $this->calculoNovedades = new CalculoNovedades(
            $salary,
            $fechaInicio,
            $fechaFin
        );
        $this->calculoNovedades->existenVacaciones(
            PayrollFactor::vacations(self::$funcionario, $fechaInicio)
        );

        $this->calculoNovedades->existenNovedades(
            PayrollFactor::factors(self::$funcionario, $fechaFin)->get()
        );
    }

    public function calculate()
    {

        $this->calculoSalario->verificarFechasContrato(self::$funcionario->contractultimate->date_of_admission, self::$funcionario->contractultimate->date_end);

        $this->calculoSalario->valorSubsidioTransporte =  Company::find(Person::find(Auth()->user()->person_id)->company_worked_id)['transportation_assistance'];

        $this->calculoSalario->calcularDiasTrabajados()->calcularSalarioDiasTrabajados();

        $this->calculoSalario->calcularTransporteDiasTrabajados(self::$funcionario->contractultimate->transport_subsidy);

        return $this->calculoSalario->crearColeccion();
    }
}
