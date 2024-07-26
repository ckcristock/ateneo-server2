<?php

namespace App\Exports;

use App\Models\ProductoRemision;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DescargaExcelExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function collection()
    {
        $result = ProductoRemision::query()
            ->select('Producto.Mantis', 'Producto.Nombre_Comercial', 'Producto_Remision.Cantidad', 'Producto_Remision.Precio', 'Producto_Remision.Lote', 'Producto_Remision.Fecha_Vencimiento')
            ->join('Producto', 'Producto.Id_Producto', '=', 'Producto_Remision.Id_Producto_Factura_Venta')
            ->when($this->id >= 132, function ($query) {
                return $query->join('Inventario_Nuevo', 'Inventario_Nuevo.Id_Inventario_Nuevo', '=', 'Producto_Remision.Id_Inventario');
            })
            ->where('Producto_Remision.Id_Remision', $this->id)
            ->get();

        return $result;
    }

    public function headings(): array
    {
        return [
            'Mantis',
            'Nombre Comercial',
            'Cantidad',
            'Precio',
            'Lote',
            'Fecha de Vencimiento'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
