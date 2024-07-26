<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AttentionRoute;

class AttentionRoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        AttentionRoute::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'RIAS materno perinatal',
                'id_type_service' => 5,
                'age' => 'x >5',
                'age_min' => 5,
                'age_max' => 200,
                'gender' => 'F;',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'RIAS para la primera infancia',
                'id_type_service' => 5,
                'age' => 'x >=0 && x <6 ',
                'age_min' => 0,
                'age_max' => 6,
                'gender' => 'F;M;NB;',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'RIAS para la infancia',
                'id_type_service' => 5,
                'age' => 'x >=6 && x <12',
                'age_min' => 6,
                'age_max' => 12,
                'gender' => 'F;M;NB;',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'RIAS para la adolescencia',
                'id_type_service' => 5,
                'age' => 'x >=12 && x <18',
                'age_min' => 12,
                'age_max' => 18,
                'gender' => 'F;M;NB;',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'RIAS para la juventud',
                'id_type_service' => 5,
                'age' => 'x >=18 && x <29',
                'age_min' => 18,
                'age_max' => 29,
                'gender' => 'F;M;NB;',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'RIAS para la adultez',
                'id_type_service' => 5,
                'age' => 'x >=29 && x <60',
                'age_min' => 29,
                'age_max' => 60,
                'gender' => 'F;M;NB;',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'RIAS para la vejez',
                'id_type_service' => 5,
                'age' => 'x >=60',
                'age_min' => 60,
                'age_max' => 200,
                'gender' => 'F;M;NB;',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla AttentionRoute
        AttentionRoute::insert($data);
    }
}
