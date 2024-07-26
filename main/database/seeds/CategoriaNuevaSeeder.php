<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CategoriaNueva;

class CategoriaNuevaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla CategoriaNueva
        CategoriaNueva::truncate();

        // Obtener la fecha y hora actual
        $currentTimestamp = now();

        // Insertar los nuevos datos
        $data = [
            [
                'company_id' => 1,
                'Nombre' => 'ACTIVOS FIJOS',
                'Compra_Internacional' => 'no',
                'Aplica_Separacion_Categorias' => 'no',
                'Activo' => 1,
                'Fijo' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'company_id' => 1,
                'Nombre' => 'EPP',
                'Compra_Internacional' => 'no',
                'Aplica_Separacion_Categorias' => 'no',
                'Activo' => 1,
                'Fijo' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'company_id' => 1,
                'Nombre' => 'DOTACION',
                'Compra_Internacional' => 'no',
                'Aplica_Separacion_Categorias' => 'no',
                'Activo' => 1,
                'Fijo' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
        ];

        // Insertar los nuevos datos en la tabla CategoriaNueva
        CategoriaNueva::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
