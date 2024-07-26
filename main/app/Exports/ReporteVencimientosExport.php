<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;

class ReporteVencimientosExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $vencidos;

    public function __construct($vencidos)
    {
        $this->vencidos = $vencidos;
    }

    public function collection()
    {
        return $this->vencidos->map(function ($item) {
            return [
                'Nombre_Comercial' => $item->Nombre_Comercial ?? '',
                'Nombre_General' => $item->Nombre_General ?? '',
                'Lote' => $item->Lote ?? '',
                'Fecha_Vencimiento' => $item->Fecha_Vencimiento ?? '',
                'Bodega' => $item->Bodega ?? '',
                'Punto' => $item->Punto ?? '',
                'Cantidad' => $item->Cantidad ?? '',
                'Costo' => $item->Costo ?? ''
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nombre Comercial',
            'Nombre General',
            'Lote',
            'Fecha Vencimiento',
            'Bodega',
            'Punto',
            'Cantidad',
            'Costo'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}