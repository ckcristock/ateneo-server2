<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\RegimenType;

class RegimenTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla RegimenType
        RegimenType::truncate();

        // Obtener la fecha y hora actual
        $currentTimestamp = now();

        // Insertar los nuevos datos
        $data = [
            [
                'short' => 'C',
                'name' => 'CONTRIBUTIVO',
                'code' => '1',
                'state' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'short' => 'S',
                'name' => 'SUBSIDIADO',
                'code' => '2',
                'state' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'short' => 'V',
                'name' => 'VINCULADO',
                'code' => '3',
                'state' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'short' => 'P',
                'name' => 'PARTICULAR',
                'code' => '4',
                'state' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'short' => 'O',
                'name' => 'OTRO',
                'code' => '5',
                'state' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
        ];

        // Insertar los nuevos datos en la tabla RegimenType
        RegimenType::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
