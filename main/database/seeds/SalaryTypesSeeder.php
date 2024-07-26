<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SalaryTypes;
use Illuminate\Support\Facades\DB;


class SalaryTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla SalaryTypes
        SalaryTypes::truncate();

        // Obtener la fecha y hora actual
        $currentTimestamp = now();

        // Insertar los nuevos datos
        $data = [
            [
                'name' => 'Mensual',
                'status' => 'activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'name' => 'Integral',
                'status' => 'activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'name' => 'Por hora',
                'status' => 'activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'name' => 'Mensual/Medio tiempo',
                'status' => 'activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
        ];

        // Insertar los nuevos datos en la tabla SalaryTypes
        SalaryTypes::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
