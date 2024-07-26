<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class AtencionTurneroExport implements FromQuery, WithHeadings
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Paciente',
            'Reclamante',
            'Hora Turno',
            'Hora Inicio Atencion',
            'Fecha',
            'Turnero',
            'Tipo',
        ];
    }
}

