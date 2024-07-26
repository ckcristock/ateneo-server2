<?php

namespace App\Http\Libs\Nomina\Facades;

use App\Http\Libs\Nomina\Calculos\CalculoLiquidacion;
use App\Http\Libs\Nomina\Calculos\CalculoIndemnizacion;
use App\Models\Company;

use App\Models\PayrollPayment;
use App\Models\Person;
use Illuminate\Support\Carbon;

/**
 * Facade para el cálcular la liquidación de x funcionario
 */
class NominaLiquidacion
{
    protected static $funcionario;
    public $auxilioTransporte = 0;
    protected $liquidacion;
    protected $ultimoPeriodoPago = [];
    protected $indemnizacion;
    protected $prestamos = [];


    /**
     * Obtener el funcionario con el id que se pasa por parámetro
     *
     * @param  Person $persona

     */
    public static function liquidacionFuncionarioWithPerson($persona)
    {
        if (gettype($persona) === 'string') {
            self::$funcionario = Person::with('work_contracts')->find($persona);
        } else {
            self::$funcionario = $persona;
        }
        //dd(self::$funcionario);
        //return self::$funcionario;
        return new self;
    }

    /**
     * Primero se obtiene el valor de auxilio de transporte configurado en la empresa, necesario para hacer cálculos de las bases de
     * prima y cesantías, siguiente se crea la configuración inicial de la indemnización en caso de que se requiera, después se calculan los dias a liquidar
     *
     * @param string $fechaFin
     * @return NominaLiquidacion
     */
    public function until($fechaFin = null)
    {

        if (self::$funcionario->work_contracts[0]->company->transportation_assistance) {
            $this->auxilioTransporte = Company::find(Person::find(Auth()->user()->person_id)->company_worked_id)['transportation_assistance'];
        }

        $this->indemnizacion = new CalculoIndemnizacion(
            self::$funcionario->work_contracts[0]->work_contract_type_id,
            self::$funcionario->work_contracts[0]->salary,
            self::$funcionario->work_contracts[0]->date_of_admission,
            self::$funcionario->work_contracts[0]->date_end
        );

        $this->liquidacion = new CalculoLiquidacion(
            self::$funcionario->work_contracts[0]->salary,
            $this->auxilioTransporte,
            self::$funcionario->work_contracts[0]->date_of_admission,
            $fechaFin,
            self::$funcionario->id
        );

        $this->liquidacion->calcularDiasLiquidacion();
        $this->liquidacion->calcularPrestamos();

        return $this;
    }

    /**
     * Se hacen los cálculos de las bases de prima y cesantías, vacaciones actuales, total por conceptos de prima, cesantias, vacaciones, y el pago total liquidación. (Ver clase CálculoLiquidación)
     *
     * @param string $fechaFin
     * @return NominaLiquidacion
     */
    public function calculate()
    {
        $this->liquidacion->calcularBaseCesantias();
        $this->liquidacion->calcularBasePrima();
        $this->liquidacion->calcularBaseVacaciones();

        //Dias acumulados hasta la fecha
        $this->liquidacion->calcularVacacionesActuales();

        //Consulta para traer los dias acumulados en vacaciones del ultimo periodo
        $this->makeConsultaPeriodo();

        //Vacaciones
        $this->liquidacion->calcularTotalVacaciones();

        //Cesantias
        $this->liquidacion->calcularDiasCesantias();
        $this->liquidacion->calcularTotalCesantias();
        $this->liquidacion->calcularTotalInteresesCesantias();

        //Prima
        $this->liquidacion->calcularDiasPrima();
        $this->liquidacion->calcularTotalPrima();

        //Total liquidación
        $this->liquidacion->calcularTotalLiquidacion();
        //Indemnización - Set salario mínimo
        $this->indemnizacion->setSalarioMinimo(
            Company::find(Person::find(Auth()->user()->person_id)->company_worked_id)['base_salary']
        );
        //Indemnización - Set fecha liquidación
        $this->indemnizacion->setFechaLiquidacion(
            $this->liquidacion->getFechaRetiro()
        );
        //Indemnización - Calcular los días laborados
        $this->indemnizacion->calcularTiempoLaborado();
        //Indemnización - Calcular el total de lad indemnización en caso de que aplique.
        $this->indemnizacion->calcularTotalIndemnizacion();


        $this->liquidacion->setIndemnizacion(
            $this->indemnizacion->getTotalIndemnizacion()
        );
        return $this;
    }

    public function makeConsultaPeriodo()
    {
        //Consulta para traer los dias acumulados en vacaciones del ultimo periodo
        $consultaPeriodo = PayrollPayment::vacacionesAcumuladasFuncionarioWithId(self::$funcionario->id)->first();

        //Extraer valor ya que se devuelve una instancia de PagoNomina
        $vacaionesUltimoPeriodo = $consultaPeriodo ? $consultaPeriodo->provisionsPersonPayrollPayment[0]['accumulated_vacations'] : null;

        //Settear los días acumulados en el último periodo
        $this->liquidacion->setVacacionesUltimoPeriodo($vacaionesUltimoPeriodo, (!$consultaPeriodo ? false : true));

        //Registrar en array las fechas del último periodo si no existen se toma el inicio del mes anterior y fin del mes actual, estos datos son necesarios para la conf del componente de calendario en el componente principal de liquidaciones (Vue)
        $this->ultimoPeriodoPago['fecha_inicio'] =  $consultaPeriodo->inicio_periodo ?? Carbon::now()->startOfMonth()->subMonth()->toDateString();
        $this->ultimoPeriodoPago['fecha_fin'] =  $consultaPeriodo->fin_periodo ?? Carbon::now()->endOfMonth()->subMonth()->toDateString();
    }

    /**
     * Se llama para calcular el pago de la liquidación con los días acumulados que se pasen por parámetro, esto cuando el usuario desee modificar los días acumulados que se calculan autmáticamente.
     *
     * @param float numeroDias
     * @return NominaLiquidacion
     */
    public function withVacacionesActuales($numeroDias)
    {
        $this->liquidacion->setVacacionesActuales($numeroDias);
        return $this;
    }

    /**
     * Se llama para calcular el pago de la liquidación con el salario base que se pase por parámetro, esto cuando el usuario desee modificar el salario base que se calcula automáticamente.
     *
     * @param integer $salario
     * @return NominaLiquidacion
     */
    public function withSalarioBase($salario = 0)
    {
        $this->liquidacion->setSalarioBase($salario);
        return $this;
    }

    /**
     * Se llama para calcular el pago de la liquidacion con la base de vacaciones que se pase por parámetro, esto cuando el usuario desee modificar la base de vacaciones que se calcula automáticamente
     *
     * @param integer $base
     * @return NominaLiquidacion
     */
    public function withBaseVacaciones($base = 0)
    {
        $this->liquidacion->setBaseVacaciones($base);
        return $this;
    }

    /**
     * Se llama para calcular el pago de la liquidación con la base de cesantias que se pase por parámetro, esto cuando el usuario desee modificar la base de cesantías que se calcula automáticamente
     *
     * @param integer $base
     * @return NominaLiquidacion
     */
    public function withBaseCesantias($base = 0)
    {
        $this->liquidacion->setBaseCesantias($base);
        return $this;
    }

    /**
     * Se llama para calcular el pago de la liquidación con la base de prima que se pase por parámetro, esto cuando el usuario desee modificar la base de prima que se calcula automáticamente
     *
     * @param integer $base
     * @return NominaLiquidacion
     */
    public function withBasePrima($base = 0)
    {
        $this->liquidacion->setBasePrima($base);
        return $this;
    }

    /**
     * Se llama para calcular el pago de la liquidación con los ingresos que se pasen por parámetro, esto cuando el usuario desee añadir otro ingresos a la liquidación
     *
     * @param integer $ingresos
     * @return NominaLiquidacion
     */
    public function withIngresos($ingresos = 0)
    {
        $this->liquidacion->setIngresos($ingresos);
        return $this;
    }

    /**
     * Se llama para calcular el pago de la liquidación con los ingresos que se pasen por parámetro, esto cuando el usuario desee restar egresos a la liquidación
     *
     * @param integer $egresos
     * @return NominaLiquidacion
     */
    public function withEgresos($egresos = 0)
    {
        $this->liquidacion->setEgresos($egresos);
        return $this;
    }


    /**
     * Getter último periodo
     *
     * @return array
     */
    public function getUltimoPeriodoPago()
    {
        return $this->ultimoPeriodoPago;
    }

    public function makeResponse()
    {
        return response()->json([
            'nombres' => self::$funcionario->first_name . ' ' . self::$funcionario->second_name,
            'apellidos' => self::$funcionario->first_surname . ' ' . self::$funcionario->second_surname,
            'identidad' => self::$funcionario->identifier,
            'cargo' => self::$funcionario->work_contracts[0]->position->name,
            'salario' => $this->liquidacion->getSalarioBase(),
            'base_cesantias' => $this->liquidacion->getBaseCesantias(),
            'base_prima' => $this->liquidacion->getBasePrima(),
            'base_vacaciones' => $this->liquidacion->getBaseVacaciones(),
            'auxilio_transporte' => $this->auxilioTransporte,
            'prestamos' => $this->liquidacion->getPrestamos(),
            'fecha_ingreso' => self::$funcionario->work_contracts[0]->date_of_admission,
            'fecha_retiro' => $this->liquidacion->getFechaRetiro(),
            'dias_liquidacion' => $this->liquidacion->getDiasLiquidacion(),
            'vacaciones_actuales' => $this->liquidacion->getVacacionesActuales(),
            'vacaciones_ultimo_periodo' => $this->liquidacion->getVacacionesUltimoPeriodo(),
            'ultimo_periodo' => $this->getUltimoPeriodoPago(),
            'total_vacaciones' => $this->liquidacion->getTotalVacaciones(),
            'total_cesantias' => $this->liquidacion->getTotalCesantias(),
            'total_intereses_cesantias' => $this->liquidacion->getTotalInteresesCesantias(),
            'total_prima' => $this->liquidacion->getTotalPrima(),
            'total_ingresos' => $this->liquidacion->getTotalIngresos(),
            'total_egresos' => $this->liquidacion->getTotalEgresos(),
            'total_indemnizacion' => $this->indemnizacion->getTotalIndemnizacion(),
            'total_liquidacion' => $this->liquidacion->getTotalLiquidacion(),
            'total_liquidacion_indemnizacion' => $this->liquidacion->getTotalLiquidacionWithIndemnizacion()
        ]);
    }
}
