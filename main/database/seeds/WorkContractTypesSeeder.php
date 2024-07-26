<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkContractType;

class WorkContractTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        WorkContractType::truncate();

        $data = [
            [
                'id' => 1,
                'conclude' => 0,
                'modified' => 0,
                'name' => 'Indefinido',
                'description' => null,
                'template' => null,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'conclude' => 1,
                'modified' => 0,
                'name' => 'Fijo',
                'description' => null,
                'template' => null,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'conclude' => 0,
                'modified' => 0,
                'name' => 'Obra/Labor',
                'description' => null,
                'template' => null,
                'status' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Inserta los datos en la tabla WorkContractType
        WorkContractType::insert($data);
    }
}
