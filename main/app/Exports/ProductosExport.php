<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductosExport implements FromCollection, WithHeadings
{
    protected $productos;
    protected $includeLote;
    protected $includeFechaVencimiento;

    public function __construct(array $productos)
    {
        $this->productos = $productos;
        $this->includeLote = false;
        $this->includeFechaVencimiento = false;

        // Verificar si algún producto tiene Lote o Fecha_Vencimiento
        foreach ($productos as $producto) {
            if (isset($producto['Lote'])) {
                $this->includeLote = true;
            }
            if (isset($producto['Fecha_Vencimiento'])) {
                $this->includeFechaVencimiento = true;
            }
        }
    }

    public function collection()
    {
        return collect($this->productos)->map(function ($item) {
            $data = [
                'Nombre Comercial' => $item['Nombre_Comercial'] ?? 'N/A',
                'Producto' => $item['Nombre_Producto'] ?? 'N/A',
                'Cantidad Inventario' => $item['Cantidad_Inventario'] ?? 'N/A',
                'Primer Conteo' => $item['Cantidad_Encontrada'] ?? 'N/A',
                'Diferencia' => $item['Cantidad_Diferencial'] ?? 'N/A',
                'Segundo Conteo' => $item['Segundo_Conteo'] ?? 'N/A',
                'Cantidad Final' => $item['Cantidad_Final'] ?? 'N/A',
            ];

            if ($this->includeLote) {
                $data['Lote'] = $item['Lote'] ?? 'N/A';
            }

            if ($this->includeFechaVencimiento) {
                $data['Fecha de Vencimiento'] = $item['Fecha_Vencimiento'] ?? 'N/A';
            }

            return $data;
        });
    }

    public function headings(): array
    {
        $headings = [
            'Nombre Comercial',
            'Producto',
            'Cantidad Inventario',
            'Primer Conteo',
            'Diferencia',
            'Segundo Conteo',
            'Cantidad Final'
        ];

        if ($this->includeLote) {
            $headings[] = 'Lote';
        }

        if ($this->includeFechaVencimiento) {
            $headings[] = 'Fecha de Vencimiento';
        }

        return $headings;
    }
}
