<?php

namespace App\Exports;

use App\Services\DotationDownloadService;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\InventaryDotation;


class DotationExport implements FromCollection, ShouldAutoSize, WithEvents
{
    private $filter;
    public function __construct($request)
    {
        $this->filter = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Obtener todos los datos sin filtrar
        $allData = DotationDownloadService::getDotation();

        // Aplicar filtros si están presentes
        if ($this->filter->filled('tipo')) {
            $tipo = $this->normalizeString($this->filter->tipo);
            $allData = $allData->filter(function ($item) use ($tipo) {
                return $this->normalizeString($item->type) === $tipo;
            });
        }

        if ($this->filter->filled('calidad')) {
            $calidad = $this->normalizeString($this->filter->calidad);
            $allData = $allData->filter(function ($item) use ($calidad) {
                return $this->normalizeString($item->status) === $calidad;
            });
        }

        if ($this->filter->filled('talla')) {
            $talla = $this->normalizeString($this->filter->talla);
            $allData = $allData->filter(function ($item) use ($talla) {
                return $this->normalizeString($item->size) === $talla;
            });
        }

        if ($this->filter->filled('nombre')) {
            $nombre = $this->normalizeString($this->filter->nombre);
            $allData = $allData->filter(function ($item) use ($nombre) {
                return strpos($this->normalizeString($item->name), $nombre) !== false;
            });
        }

        // Transformar la colección para cumplir con el formato deseado
        $formattedData = $allData->map(function ($item) {
            return [
                $item->name,
                $item->size,
                $item->type,
                $item->status,
                $item->stock
            ];
        });

        $headings = [
            'NOMBRE',
            'TALLA',
            'TIPO',
            'ESTADO',
            'CANTIDAD'
        ];

        // Definir nombres legibles para los filtros
        $filterNames = [
            'tipo' => 'TIPO:',
            'calidad' => 'CALIDAD:',
            'talla' => 'TALLA:',
            'nombre' => 'NOMBRE:'
        ];

        // Crear una colección para almacenar los filtros
        $filters = new Collection();

        // Agregar los filtros a la colección de filtros
        foreach ($this->filter->all() as $key => $value) {
            if (array_key_exists($key, $filterNames) && $value) {
                $filters->push([$filterNames[$key], $value]);
            }
            if ($value) {
                $allData = $allData->where($key, $value);
            }
        }


        $formattedData->prepend($headings);
        $formattedData->prepend(['']);

        $filters->each(function ($filter) use ($formattedData) {
            $formattedData->prepend([$filter[0], $filter[1]]);
        });

        $formattedData->prepend(['FILTRADO POR:']);
        $formattedData->prepend(["INVENTARIO DOTACIÓN "]);

        return $formattedData;
    }

    // Función para normalizar cadenas de caracteres
    private function normalizeString($string)
    {
        $string = mb_strtolower($string, 'UTF-8'); // Convertir a minúsculas
        $string = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü'], ['a', 'e', 'i', 'o', 'u', 'u'], $string); // Reemplazar tildes y diéresis
        $string = preg_replace('/[^a-z0-9]/', '', $string); // Eliminar caracteres especiales
        return $string;
    }




    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);

                // Contar solo los filtros que tienen valores definidos
                $filterCount = collect($this->filter->all())->filter()->count();

                $sheet->getStyle('A3:A' . (3 + $filterCount))->getFont()->setBold(true)->setSize(12);

                $sheet->getStyle('A' . (4 + $filterCount) . ':F' . (4 + $filterCount))->getFont()->setBold(true)->setSize(12);
            },
        ];
    }
}
