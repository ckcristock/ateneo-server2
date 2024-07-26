<?php

// namespace Database\Seeders;

use App\Models\Company;
use App\Models\ComprobanteConsecutivo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComprobanteConsecutivoNoConformeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtén todos los registros de la tabla company
        $companies = Company::all();

        $comprobantesConsecutivos = [
            [
                'tipo' => 'No conformidad',
                'prefijo' => 'NC',
                'format_code' => 'V1',
                'table_name' => 'No_Conforme',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Remision',
                'prefijo' => 'REM',
                'format_code' => 'V1',
                'table_name' => 'Remision',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
        ];


        // Inserta los datos de comprobantes_consecutivos para cada registro de company
        foreach ($companies as $company) {
            foreach ($comprobantesConsecutivos as $comprobante) {
                $comprobanteConsecutivo = new ComprobanteConsecutivo($comprobante);
                $comprobanteConsecutivo->company_id = $company->id;
                $comprobanteConsecutivo->save();
            }
        }
    }
}
