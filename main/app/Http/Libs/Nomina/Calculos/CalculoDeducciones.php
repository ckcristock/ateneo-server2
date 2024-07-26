<?php

namespace App\Http\Libs\Nomina\Calculos;

use Illuminate\Support\Collection;

/** Clase para calcular las deducciones */
class CalculoDeducciones implements Coleccion
{
    protected $deducciones;
    protected $totalDeducciones;
    protected $deduccionesRegistradas;
    protected $sumaDeducctiones = 0;
    protected $sumaPrestamos = 0;
    //protected $conceptoAux = 0;

    /**Deductions,
     * loan_fees: los prestamos que tiene pendiente el funcionario deben ser descontados en la nómina
     */

    /**
     * Constructor
     *
     * @param Collection $deducciones
     */
    public function __construct(Collection $deducciones)
    {
        $this->deducciones = $deducciones;
        $this->deduccionesRegistradas = collect([]);
        $this->totalDeducciones = 0;
        if (isset($deducciones->all()[0]->type)) {
            $this->getPrestamosCustom();
        }
        if (isset($deducciones->all()[0]->deduccion)) {
            $this->getDeduccionesCustom();
        }
    }

    public function getDeducciones()
    {
        return $this->deducciones;
    }

    public function getDeduccionesRegistradas()
    {
        if (collect($this->deduccionesRegistradas)->isEmpty()) {
            return null;
        }
        return $this->deduccionesRegistradas;
    }

    /**
     * Calcular el valor total de las deducciones
     *
     * @return void
     */
    public function calcularTotalDeducciones()
    {
        if ($this->deducciones->isNotEmpty()) {
            //$this->totalDeducciones = $this->deducciones->sum('value');
            $this->totalDeducciones = $this->sumaDeducctiones + $this->sumaPrestamos;
        }
    }

    /**
     * Getter para el total de las deducciones
     *
     * @return float
     */
    public function getTotalDeducciones()
    {
        return $this->totalDeducciones;
    }

    /**
     * Aplicar el contract de la interfaz, crear la colección
     *
     * @return \Illuminate\Support\Collection
     */
    public function crearColeccion()
    {
        return new Collection([
            'valor_total' => $this->getTotalDeducciones(),
            'deducciones' => $this->getDeduccionesRegistradas()
        ]);
    }

    public function crearCustomColeccion()
    {
        return new Collection([
            'valor_total' => $this->getTotalDeducciones(),
            'deducciones' => $this->deducciones
        ]);
    }

    public function getDeduccionesCustom()
    {
        $conceptoAux = '';
        foreach ($this->deducciones->groupBy('deduccion.concept') as $deduccion) {
            $this->sumaDeducctiones = 0;
            foreach ($deduccion as $concepto) {
                $this->sumaDeducctiones += $concepto->value;
                //$this->conceptoAux = $concepto;
                $conceptoAux = $concepto->deduccion->concept;
            }
            //$this->deduccionesRegistradas->put($this->conceptoAux->deduccion->concept, $this->sumaDeducctiones);
            $this->deduccionesRegistradas->put($conceptoAux, $this->sumaDeducctiones);
        }
    }

    public function getPrestamosCustom()
    {
        $conceptoAuxPres = '';
        foreach ($this->deducciones->groupBy('fees.loan_id') as $prestamo) {
            foreach ($prestamo as $concepto) {
                $conceptoAuxPres = $concepto->type;
                foreach ($concepto->fees as $fee) {
                    $this->sumaPrestamos += $fee->value;
                }
            }
            $this->deduccionesRegistradas->put($conceptoAuxPres, $this->sumaPrestamos);
        }
    }
}
