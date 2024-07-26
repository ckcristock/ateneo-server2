<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PensionFund;
use Illuminate\Support\Facades\DB;

class PensionFundsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PensionFund::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'PROTECCION',
                'code' => '230201',
                'nit' => '800138188-1',
                'status' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'PORVENIR',
                'code' => '230301',
                'nit' => '800144331-3',
                'status' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'OLD MUTUAL ALTERNATIVO',
                'code' => '230904',
                'nit' => '800253055-2',
                'status' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'COLFONDOS',
                'code' => '231001',
                'nit' => '800149496-2',
                'status' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'COLPENSIONES',
                'code' => '25-14',
                'nit' => '900336004-7',
                'status' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla PensionFund
        PensionFund::insert($data);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
