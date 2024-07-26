<?php

namespace App\Exports;

use App\Models\PayrollFactor;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;


class PayrollFactorExport implements FromCollection, ShouldAutoSize, WithEvents
{
    private $filter;
    private $company_id;

    public function __construct($dates, $company_id)
    {
        $this->filter = $dates;
        $this->company_id = $company_id;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = DB::table('payroll_factors as pf')
            ->join('people as p', 'pf.person_id', '=', 'p.id')
            ->join('work_contracts as w', function ($join) {
                $join->on('w.person_id', '=', 'p.id')
                    ->where('w.liquidated', 0)
                    ->where('w.company_id', $this->company_id);
            })
            ->join('disability_leaves as dl', 'pf.disability_leave_id', '=', 'dl.id')
            ->select(
                DB::raw("CONCAT(p.first_name, ' ', p.first_surname) AS full_name"),
                DB::raw('DATE_FORMAT(pf.created_at, "%d-%m-%Y") AS created_at'),
                'dl.concept',
                'pf.observation',
                DB::raw('DATE_FORMAT(pf.date_start, "%d-%m-%Y") AS date_start'),
                DB::raw('DATE_FORMAT(pf.date_end, "%d-%m-%Y") AS date_end'),
            )
            ->orderByDesc('pf.date_start');

        if ($personfill = request()->get('personfill')) {
            $query->where('pf.person_id', 'like', '%' . $personfill . '%');
        }

        $date_start = request()->get('date_start');
        $date_end = request()->get('date_end');
        if ($date_start && $date_end) {
            $query->where('pf.date_start', '>=', $date_start)
                ->where('pf.date_end', '<=', $date_end);
        }

        if ($type = request()->get('type')) {
            $query->where('pf.disability_leave_id', 'like', '%' . $type . '%');
        }

        $data = $query->get();
        $headings = [
            'NOMBRES Y APELLIDOS',
            'FECHA DE CREACIÓN',
            'NOVEDAD',
            'DESCRIPCIÓN',
            'INICIO',
            'FIN'
        ];

        $filterNames = [
            'date_start' => 'FECHA DE INICIO:',
            'date_end' => 'FECHA DE FIN:',
            'personfill' => 'IDENTIFICACIÓN:',
            'type' => 'NOVEDAD:',
        ];

        $data->prepend($headings);
        $data->prepend(['']);

        $filterData = [];
        foreach ($this->filter->all() as $key => $value) {
            if (isset($filterNames[$key])) {
                $filterData[] = [$filterNames[$key], $value];
            }
        }
        $data->prepend($filterData);
        $data->prepend(['FILTRADO POR:']);
        $data->prepend(["NOVEDADES "]);

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
                $filterCount = count($this->filter->all());

                $sheet->getStyle('A3:A' . (3 + $filterCount - 1))->getFont()->setBold(true)->setSize(12); // Tamaño 12
    
                $sheet->getStyle('A' . (4 + $filterCount) . ':F' . (4 + $filterCount))->getFont()->setBold(true)->setSize(12); // Tamaño 12
            },
        ];
    }

}
