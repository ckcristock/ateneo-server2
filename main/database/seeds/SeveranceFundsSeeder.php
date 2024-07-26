<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SeveranceFund;
use Illuminate\Support\Facades\DB;

class SeveranceFundsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla SeveranceFund
        SeveranceFund::truncate();

        // Obtener la fecha y hora actual
        $currentTimestamp = now();

        // Insertar los nuevos datos
        $data = [
            [
                'name' => 'COLFONDOS',
                'code' => '231001',
                'nit' => '800149496-2',
                'status' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'name' => 'PORVENIR',
                'code' => '230301',
                'nit' => '800144331-3',
                'status' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'name' => 'PROTECCION',
                'code' => '230201',
                'nit' => '800138188-1',
                'status' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'name' => 'OLD MUTUAL ALTERNATIVO',
                'code' => '230904',
                'nit' => '800253055-2',
                'status' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'name' => 'FONDO NACIONAL DEL AHORRO',
                'code' => '270000',
                'nit' => '899999284-4',
                'status' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'name' => 'COLPENSIONES',
                'code' => '25-14',
                'nit' => '900336004-7',
                'status' => 'Activo',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
        ];

        // Insertar los nuevos datos en la tabla SeveranceFund
        SeveranceFund::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
