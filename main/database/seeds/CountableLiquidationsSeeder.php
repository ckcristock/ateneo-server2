<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CountableLiquidation;

class CountableLiquidationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla CountableLiquidation
        CountableLiquidation::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'concept' => 'Indemnización por retiro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concept' => 'Otros ingresos por liquidación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los nuevos datos en la tabla CountableLiquidation
        CountableLiquidation::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
