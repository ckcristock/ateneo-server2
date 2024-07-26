<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PayrollRisksArl;
use Illuminate\Support\Facades\DB;

class PayrollRisksArlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla PayrollRisksArl
        PayrollRisksArl::truncate();

        // Obtener la fecha y hora actual
        $currentTimestamp = now();

        // Insertar los nuevos datos
        $data = [
            [
                'prefix' => 'riesgo_uno',
                'concept' => 'Riesgo ARL I',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'prefix' => 'riesgo_dos',
                'concept' => 'Riesgo ARL II',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'prefix' => 'riesgo_tres',
                'concept' => 'Riesgo ARL III',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'prefix' => 'riesgo_cuatro',
                'concept' => 'Riesgo ARL IV',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'prefix' => 'riesgo_cinco',
                'concept' => 'Riesgo ARL V',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
        ];

        // Insertar los nuevos datos en la tabla PayrollRisksArl
        PayrollRisksArl::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
