<?php

// namespace Database\Seeders;

use App\Models\TypeService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        TypeService::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'Consultas básicas',
                'is_service' => 'y',
            ],
            [
                'id' => 2,
                'name' => 'Consultas especialidades básicas',
                'is_service' => 'y',
            ],
            [
                'id' => 3,
                'name' => 'Consultas especialidades',
                'is_service' => 'y',
            ],
            [
                'id' => 4,
                'name' => 'Consultas subespecialidades',
                'is_service' => 'y',
            ],
            [
                'id' => 5,
                'name' => 'PyD (Protección específica y Detección temprana)',
                'is_service' => 'y',
            ],
            [
                'id' => 6,
                'name' => 'Rehabilitación',
                'is_service' => 'y',
            ],
            [
                'id' => 7,
                'name' => 'Procedimientos',
                'is_service' => 'y',
            ],
            [
                'id' => 8,
                'name' => 'Laboratorio',
                'is_service' => 'y',
            ],
            [
                'id' => 9,
                'name' => 'Orientación e información al usuario',
                'is_service' => 'n',
            ],
            [
                'id' => 10,
                'name' => 'PQRS',
                'is_service' => 'n',
            ],
            [
                'id' => 11,
                'name' => 'No aplica servicio',
                'is_service' => 'n',
            ],
        ];

        // Añadir los timestamps
        foreach ($data as &$item) {
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        // Inserta los datos en la tabla TypeService
        TypeService::insert($data);
    }
}
