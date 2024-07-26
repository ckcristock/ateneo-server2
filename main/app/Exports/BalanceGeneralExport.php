<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class BalanceGeneralExport implements FromView, ShouldAutoSize
{
    private $balance;
    private $resultado_ejercicio;
    private $total_patrimonio_utilidad_ejercicio;
    private $total_pasivo_y_patrimonio;
    private $fecha;
    private $tipo_reporte;
    private $fecha_corte;
    private $tipo_reporte_2;
    private $nivel_reporte;
    private $cod_clase_temp;
    private $total_patrimonio;
    private $acum_saldos;
    public function __construct(
        $balance,
        $resultado_ejercicio,
        $total_patrimonio_utilidad_ejercicio,
        $total_pasivo_y_patrimonio,
        $fecha,
        $tipo_reporte,
        $fecha_corte,
        $tipo_reporte_2,
        $nivel_reporte,
        $cod_clase_temp,
        $total_patrimonio,
        $acum_saldos
    ) {
        $this->balance = $balance;
        $this->resultado_ejercicio = $resultado_ejercicio;
        $this->total_patrimonio_utilidad_ejercicio = $total_patrimonio_utilidad_ejercicio;
        $this->total_pasivo_y_patrimonio = $total_pasivo_y_patrimonio;
        $this->fecha = $fecha;
        $this->tipo_reporte = $tipo_reporte;
        $this->fecha_corte = $fecha_corte;
        $this->tipo_reporte_2 = $tipo_reporte_2;
        $this->nivel_reporte = $nivel_reporte;
        $this->cod_clase_temp = $cod_clase_temp;
        $this->total_patrimonio = $total_patrimonio;
        $this->acum_saldos = $acum_saldos;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view('exports.BalanceGeneral', [
            'balance' => $this->balance,
            'resultado_ejercicio' => $this->resultado_ejercicio,
            'total_patrimonio_utilidad_ejercicio' => $this->total_patrimonio_utilidad_ejercicio,
            'total_pasivo_y_patrimonio' => $this->total_pasivo_y_patrimonio,
            'fecha' => $this->fecha,
            'tipo_reporte' => $this->tipo_reporte,
            'fecha_corte' => $this->fecha_corte,
            'tipo_reporte_2' => $this->tipo_reporte_2,
            'nivel_reporte' => $this->nivel_reporte,
            'cod_clase_temp' => $this->cod_clase_temp,
            'total_patrimonio' => $this->total_patrimonio,
            'acum_saldos' => $this->acum_saldos,
        ]);
    }
}
