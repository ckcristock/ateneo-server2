<?php

namespace App\Http\Libs\Nomina\Calculos;

use App\Models\WorkContractType;
use Illuminate\Support\Carbon;

/**
 * Clase para calcular el pago de la indemnización de x funcionario
 */
class CalculoIndemnizacion
{

    /**
     * Id del tipo de contrato del funcionario
     *
     * @var integer
     */
    protected $tipoContrato;

    /**
     * salario base actual del funcionario
     *
     * @var integer
     */
    protected $salario;

    /**
     * Salario mínimo que tiene configurada la empresa
     *
     * @var integer
     */
    protected $salarioMinimo;

    /**
     * Fecha de vencimiento del contrato del funcionario, en caso de que aplique
     *
     * @var string
     */
    protected $fechaFinContrato;

    /**
     * Fecha de inicio de contrato del funcionario
     *
     * @var string
     */
    protected $fechaInicioContrato;

    /**
     * Fecha en que se realiza la liquidación del funcionario
     *
     * @var string
     */
    protected $fechaLiquidacion;

    /**
     * Tiempo en días trabajado del funcionario, desde que ingresó hasta la fecha de liquidación
     *
     * @var integer
     */
    protected $tiempoLaborado;
    protected $tiempoLaboradoModified;

    /**
     * Total a pagar por indemnización al funcionario
     *
     * @var integer
     */
    protected $totalIndemnizacion;


    /**
     * Constructor de la clase recibe el id del tipo de contrato del funcionario, su salario, la fecha de inicio o ingreso, la fecha de fin si tiene término fijo
     *
     * @param integer $tipoContrato
     * @param integer $salario
     * @param string $fechaInicioContrato
     * @param string $fechaFinContrato
     */
    public function __construct($tipoContrato, $salario, $fechaInicioContrato, $fechaFinContrato = null)
    {
        $this->tipoContrato = $tipoContrato;
        $this->salario = $salario;
        $this->fechaInicioContrato = $fechaInicioContrato;
        $this->fechaFinContrato = $fechaFinContrato;
    }

    /**
     * Setter salario mínimo
     *
     * @param integer $salarioMinimo
     * @return void
     */
    public function setSalarioMinimo($salarioMinimo)
    {
        $this->salarioMinimo = $salarioMinimo;
    }

    /**
     * Setter fecha de liquidación
     *
     * @param string $fechaLiquidacion
     * @return void
     */
    public function setFechaLiquidacion($fechaLiquidacion)
    {
        $this->fechaLiquidacion = $fechaLiquidacion;
    }

    /**
     * Calcular días trabajados desde que ingresa el funcionario hasta la fecha en que se va a liquidar
     *
     * @return void
     */
    public function calcularTiempoLaborado()
    {
        $this->tiempoLaborado = Carbon::parse($this->fechaLiquidacion)->diffInDays(Carbon::parse($this->fechaInicioContrato));
        $this->tiempoLaboradoModified = $this->tiempoLaborado;
    }

    /**
     * Calcular el total a pagar por indemnización, dependiendo del tipo de contrato (fijo o indefinido)
     *
     * @return void
     */
    public function calcularTotalIndemnizacion()
    {
        $wct = WorkContractType::get();
        foreach ($wct as $item) {
            if ($item['name'] == 'Fijo') {
                $fijo = $item['id'];
            } else if ($item['name'] == 'Indefinido') {
                $indefinido = $item['id'];
            }
        }
        if ($this->tipoContrato === $fijo) {
            $this->calcularTerminoFijo();
        } else if ($this->tipoContrato == $indefinido) {
            $this->calcularTerminoIndefinido();
        }
    }

    /**
     * Calcular el total a pagar por indemnización por contrato a término indefinido
     *
     * @return void
     */
    public function calcularTerminoIndefinido()
    {
        if ($this->tiempoLaborado < 365 && $this->salario < $this->salarioMinimo * 10) {
            $this->totalIndemnizacion = round(($this->salario / 30) * 30, 0, PHP_ROUND_HALF_UP);
        } else if ($this->tiempoLaborado < 365 && $this->salario >= $this->salarioMinimo * 10) {
            $this->totalIndemnizacion = round(($this->salario / 30) * 20, 0, PHP_ROUND_HALF_UP);
        } else if ($this->tiempoLaborado > 360 && $this->salario < $this->salarioMinimo * 10) {
            $this->totalIndemnizacion = round(($this->salario / 30) * 30, 0, PHP_ROUND_HALF_UP);
            $this->tiempoLaboradoModified -= 365;

            $diasRestantes = (20 * $this->tiempoLaboradoModified) / 365;
            $this->totalIndemnizacion += round(($this->salario / 30) * $diasRestantes, 0, PHP_ROUND_HALF_UP);
        } else if ($this->tiempoLaborado > 365 && $this->salario >= $this->salarioMinimo * 10) {
            $this->totalIndemnizacion = round(($this->salario / 30) * 20, 0, PHP_ROUND_HALF_UP);
            $this->tiempoLaboradoModified -= 365;

            $diasRestantes = (15 * $this->tiempoLaboradoModified) / 365;
            $this->totalIndemnizacion += round(($this->salario / 30) * $diasRestantes, 0, PHP_ROUND_HALF_UP);
        }
    }

    /**
     * Calcular el total a pagar por indemnización por contrato a término fijo
     *
     * @return void
     */
    public function calcularTerminoFijo()
    {
        $diasAPagar = 0;
        if (Carbon::parse($this->fechaFinContrato) > Carbon::parse($this->fechaLiquidacion)) {
            $diasAPagar = Carbon::parse($this->fechaFinContrato)->diffInDays(Carbon::parse($this->fechaLiquidacion));
            $this->totalIndemnizacion = round(($this->salario / 30) * $diasAPagar, 0, PHP_ROUND_HALF_UP);
        } else {
            $this->totalIndemnizacion = 0;
        }
    }

    /**
     * Getter tiempo laborado
     *
     * @return integer
     */
    public function getTiempoLaborado()
    {
        return $this->tiempoLaborado;
    }

    /**
     * Getter total indemnización
     *
     * @return integer
     */
    public function getTotalIndemnizacion()
    {
        return $this->totalIndemnizacion;
    }
}
