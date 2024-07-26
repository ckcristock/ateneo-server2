<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DisabilityPercentage;
use Illuminate\Support\Facades\DB;

class DisabilityPercentagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla DisabilityPercentage
        DisabilityPercentage::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'name' => 'Al 100 % primeros 2 dias y desde el dia 3 al 66,67%',
                'value' => 'Al 100 % primeros 2 dias y desde el dia 3 al 66,67%',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Al 66,66 del dia uno al 90',
                'value' => 'Al 66,66 del dia uno al 90',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Al 100% del dia uno al 90',
                'value' => 'Al 100% del dia uno al 90',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los nuevos datos en la tabla DisabilityPercentage
        DisabilityPercentage::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
