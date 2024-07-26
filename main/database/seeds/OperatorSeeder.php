<?php

// namespace Database\Seeders;

use App\Models\Operator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla TipoRechazo
        Operator::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'name' => 'is',
            ],
            [
                'name' => 'is not',
            ],
            [
                'name' => 'is set',
            ],
            [
                'name' => 'is not set',
            ],
        ];

        // Insertar los nuevos datos en la tabla TipoRechazo
        Operator::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
