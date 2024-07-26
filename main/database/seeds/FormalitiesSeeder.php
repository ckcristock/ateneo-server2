<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Formality;

class FormalitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        Formality::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'Cita primera vez',
                'component' => 'Asignar Citas',
                'hasTypeServices' => 1,
                'hasAmbits' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Cita control',
                'component' => 'Asignar Citas',
                'hasTypeServices' => 1,
                'hasAmbits' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Reasignación de citas',
                'component' => 'Reasignar Citas',
                'hasTypeServices' => 0,
                'hasAmbits' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Cancelación de citas',
                'component' => 'Reasignar Citas',
                'hasTypeServices' => 0,
                'hasAmbits' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Consulta información citas',
                'component' => 'Reasignar Citas',
                'hasTypeServices' => 0,
                'hasAmbits' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Otro',
                'component' => 'Tipificar',
                'hasTypeServices' => 1,
                'hasAmbits' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla Formality
        Formality::insert($data);
    }
}
