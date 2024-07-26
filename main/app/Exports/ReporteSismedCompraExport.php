<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteSismedCompraExport implements FromCollection, WithHeadings
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
            'Precio de Regulación',
            'Máximo',
            'Mínimo',
            'Factura Máxima',
            'Factura Mínima',
            'Precio',
            'Cantidad',
            'Nombre del Producto',
            'Cufe',
            'Índice',
        ];
    }
}

