<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TipoRechazo;

class TipoRechazoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla TipoRechazo
        TipoRechazo::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'Nombre' => 'Costo incorrecto',
            ],
            [
                'Nombre' => 'Proveedor incorrecto',
            ],
            [
                'Nombre' => 'Productos incorrectos',
            ],
        ];

        // Insertar los nuevos datos en la tabla TipoRechazo
        TipoRechazo::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
