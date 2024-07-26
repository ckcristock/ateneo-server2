<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Impuesto;
use Illuminate\Support\Facades\DB;

class ImpuestoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla Impuesto
        Impuesto::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'Valor' => 0,
            ],
            [
                'Valor' => 19,
            ],
        ];

        // Insertar los nuevos datos en la tabla Impuesto
        Impuesto::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
