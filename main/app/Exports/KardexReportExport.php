<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KardexReportExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;
    protected $variablesLabels;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->variablesLabels = array_unique($data['variables_labels'] ?? []);
    }

    public function array(): array
    {
        $resultados = $this->data['resultados'] ?? [];
        $total = $this->data['total'] ?? 0;
        $acum = $total;

        $rows = [];
        foreach ($resultados as $res) {
            $tipo = $res['Tipo'] ?? '';
            $cantidad = $res['Cantidad'] ?? 0;

            if ($tipo == 'Entrada') {
                $acum += $cantidad;
            } elseif ($tipo == 'Salida') {
                $acum -= $cantidad;
            } elseif ($tipo == 'Inventario') {
                $acum = $cantidad;
            }

            // Variables dinámicas
            $variables = $res['variables'] ?? [];
            $variablesValues = array_map(function ($label) use ($variables) {
                return $variables[$label] ?? '';
            }, $this->variablesLabels);

            $rows[] = array_merge([
                $res['Fecha'] ?? '',
                $tipo,
                $res['Codigo'] ?? '',
                $res['Factura'] ?? '',
                $res['Nombre_Origen'] ?? '',
                $res['Destino'] ?? '',
                $res['Lote'] ?? '',
                $res['Fecha_Vencimiento'] ?? '',
                $tipo == 'Entrada' ? $cantidad : '',
                $tipo == 'Salida' ? $cantidad : '',
                $acum,
            ], $variablesValues);
        }

        return $rows;
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Fecha', 'Tipo', 'Codigo', 'Factura', 'Origen', 'Destino', 'Lote', 'Fecha vencimiento', 'Entrada', 'Salida', 'Saldo'
        ];

        return array_merge($baseHeadings, $this->variablesLabels);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getDefaultColumnDimension()->setAutoSize(true);
    }
}

