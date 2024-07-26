<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeLocation;
use Illuminate\Support\Facades\DB;

class TypeLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla TypeLocation
        TypeLocation::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'id' => 1,
                'show_company_owners' => 1,
                'name' => 'Propio',
                'description' => 'sede propia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'show_company_owners' => 0,
                'name' => 'Tercero',
                'description' => 'tercero',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los nuevos datos en la tabla TypeLocation
        TypeLocation::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
