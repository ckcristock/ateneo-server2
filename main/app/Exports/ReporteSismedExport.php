<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteSismedExport implements FromCollection, WithHeadings
{
    protected $resultado;

    public function __construct($resultado)
    {
        $this->resultado = $resultado;
    }

    public function collection()
    {
        return collect($this->resultado);
    }

    public function headings(): array
    {
        return [
            'Número',
            'Mes',
            'Tipo',
            'Código Cum',
            'Id Producto',
            'Precio de Regulación',
            'Máximo',
            'Mínimo',
            'Factura Máxima',
            'Factura Mínima',
            'Precio',
            'Cantidad',
            'Costo',
            'Nombre del Producto',
            'Cufe',
            'Índice',
        ];
    }
}
