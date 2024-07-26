<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class PatientsTemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            [
                'ID TIPO DE DOCUMENTO',
                'NÚMERO DE DOCUMENTO',
                'PRIMER APELLIDO',
                'SEGUNDO APELLIDO',
                'PRIMER NOMBRE',
                'SEGUNDO NOMBRE',
                'FECHA DE NACIMIENTO',
                'GÉNERO',
                'ID NIVEL',
                'DIRECCIÓN',
                'TELÉFONO'
            ]
        ];
    }
}
