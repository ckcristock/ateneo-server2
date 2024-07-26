<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PayrollSocialSecurityPeople;

class PayrollSocialsSecurityPeopleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        PayrollSocialSecurityPeople::truncate();

        $data = [
            [
                'id' => 1,
                'prefix' => 'salud',
                'concept' => 'Salud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'prefix' => 'pension',
                'concept' => 'Pensión',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'prefix' => 'fondo_solidaridad',
                'concept' => 'Fondo de solidaridad',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'prefix' => 'fondo_subsistencia_02',
                'concept' => 'Fondo de subsistencia 16 a 17 SMMLV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'prefix' => 'fondo_subsistencia_04',
                'concept' => 'Fondo de subsistencia 17 a 18 SMMLV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'prefix' => 'fondo_subsistencia_06',
                'concept' => 'Fondo de subsistencia 18 a 19 SMMLV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'prefix' => 'fondo_subsistencia_08',
                'concept' => 'Fondo de subsistencia 19 a 20 SMMLV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'prefix' => 'fondo_subsistencia_1',
                'concept' => 'Fondo de subsistencia mayor a 20 SMMLV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla PayrollSocialSecurityPeople
        PayrollSocialSecurityPeople::insert($data);
    }
}
