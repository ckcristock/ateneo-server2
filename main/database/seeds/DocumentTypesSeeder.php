<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentTypes;

class DocumentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        DocumentTypes::truncate();

        $data = [
            [
                'id' => 1,
                'code' => 'CC',
                'dian_code' => 13,
                'name' => 'Cédula de Ciudadanía',
                'status' => 'activo',
            ],
            [
                'id' => 2,
                'code' => 'NIT',
                'dian_code' => 31,
                'name' => 'Número de Identificación Tributaria',
                'status' => 'activo',
            ],
            [
                'id' => 3,
                'code' => 'CE',
                'dian_code' => 22,
                'name' => 'Cédula de Extranjería',
                'status' => 'activo',
            ],
            [
                'id' => 4,
                'code' => 'RC',
                'dian_code' => 11,
                'name' => 'Registro Civil',
                'status' => 'activo',
            ],
            [
                'id' => 5,
                'code' => 'TI',
                'dian_code' => 12,
                'name' => 'Tarjeta de Identidad',
                'status' => 'activo',
            ],
            [
                'id' => 6,
                'code' => 'TE',
                'dian_code' => 21,
                'name' => 'Tarjeta de Extranjería',
                'status' => 'activo',
            ],
            [
                'id' => 7,
                'code' => 'PA',
                'dian_code' => 41,
                'name' => 'Pasaporte',
                'status' => 'activo',
            ],
            [
                'id' => 8,
                'code' => 'DE',
                'dian_code' => 42,
                'name' => 'Documento de Identificación Extranjero',
                'status' => 'activo',
            ],
            [
                'id' => 9,
                'code' => 'PEP',
                'dian_code' => 47,
                'name' => 'Permiso Especial de Permanencia',
                'status' => 'activo',
            ],
            [
                'id' => 10,
                'code' => 'NIT d',
                'dian_code' => 50,
                'name' => 'NIT de otro país',
                'status' => 'activo',
            ],
            [
                'id' => 11,
                'code' => 'NUIP',
                'dian_code' => 91,
                'name' => 'NUIP',
                'status' => 'activo',
            ],
        ];

        // Añadir los timestamps
        foreach ($data as &$item) {
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        // Inserta los datos en la tabla DocumentTypes
        DocumentTypes::insert($data);
    }
}
