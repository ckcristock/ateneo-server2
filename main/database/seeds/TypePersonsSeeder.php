<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypePerson;
use Illuminate\Support\Facades\DB;

class TypePersonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla TypePerson
        TypePerson::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'name' => 'ADMINISTRADOR',
                'description' => null,
            ],
            [
                'name' => 'FUNCIONARIOS',
                'description' => null,
            ],
            [
                'name' => 'PROFESIONALES',
                'description' => null,
            ],
        ];

        // Insertar los nuevos datos en la tabla TypePerson
        TypePerson::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
