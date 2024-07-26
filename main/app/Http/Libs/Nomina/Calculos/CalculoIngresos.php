<?php

namespace App\Http\Libs\Nomina\Calculos;

use Illuminate\Support\Collection;


/**
 * Clase para el cálculo de los ingresos tanto constitutivos como no constitutivos
 */
class CalculoIngresos implements Coleccion
{
    private $ingresos = [];
    private $constitutivos = [];
    private $noConstitutivos = [];


    /**
     * constructor
     *
     */
    public function __construct($ingresos = [])
    {
        $this->ingresos = is_array($ingresos) ? $ingresos : [];
    }

    /**
     * Getter del array o collection de ingresos
     *
     * @return array
     */
    public function getIngresos()
    {
        return $this->ingresos;
    }

    /**
     * Registrar los ingresos en el container
     *
     * @return void
     */
    public function registrarIngresos()
    {
        foreach ($this->ingresos as $index => $ingreso) {
            if ($ingreso->ingreso->type == 'Constitutivo') {
                $this->constitutivos[$ingreso->ingreso->concept][$index] = $ingreso->value;
            } else {
                $this->noConstitutivos[$ingreso->ingreso->concept][$index] = $ingreso->value;
            }
        }
        foreach ($this->constitutivos as $key => $item) {
            $this->constitutivos[$key] = array_sum($this->constitutivos[$key]);
        }
        foreach ($this->noConstitutivos as $key => $item) {
            $this->noConstitutivos[$key] = array_sum($this->noConstitutivos[$key]);
        }
    }

    /**
     * Getter para ingresos constitutivos
     *
     */
    public function getConstitutivos()
    {
        return $this->constitutivos;
    }

    /**
     * Getter para ingresos no constitutivos
     *
     */
    public function getNoconstitutivos()
    {
        return $this->noConstitutivos;
    }


    /**
     * Calcular el valor total de ingresos constitutivos
     *
     * @return int
     */
    public function calcularTotalConstitutivos()
    {
        return collect($this->constitutivos)->values()->sum();
    }


    /**
     * Calcular el valor total de ingresos no constitutivos
     *
     * @return int
     */
    public function calcularTotalNoConstitutivos()
    {
        return collect($this->noConstitutivos)->values()->sum();
    }

    /**
     * Calcular el total de ingresos tanto constitutitvos como no constitutivos
     *
     * @return int
     */
    public function calcularTotalIngresos()
    {
        $coleccionIngresos = collect($this->ingresos);
        return $coleccionIngresos->keyBy('value')->keys()->sum();
    }

    /**
     * Aplicar el contract de la interfaz, crear la colección
     *
     * @return \Illuminate\Support\Collection
     */
    public function crearColeccion()
    {
        return new Collection([
            'constitutivos' => $this->getConstitutivos(),
            'no_constitutivos' => $this->getNoConstitutivos(),
            'valor_constitutivos' => $this->calcularTotalConstitutivos(),
            'valor_no_constitutivos' => $this->calcularTotalNoConstitutivos(),
            'valor_total' => $this->calcularTotalIngresos()
        ]);
    }
}
