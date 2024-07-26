<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DisabilityLeave;

class DisabilityLeavesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        DisabilityLeave::truncate();

        $data = [
            [
                'id' => 1,
                'concept' => 'Incapacidad laboral',
                'accounting_account' => '0524',
                'sum' => 1,
                'state' => 1,
                'novelty' => NULL,
                'modality' => 'Por Dia',
            ],
            [
                'id' => 2,
                'concept' => 'Incapacidad general',
                'accounting_account' => '0524',
                'sum' => 1,
                'state' => 1,
                'novelty' => NULL,
                'modality' => NULL,
            ],
            [
                'id' => 3,
                'concept' => 'Licencia de maternidad',
                'accounting_account' => '0524',
                'sum' => 1,
                'state' => 1,
                'novelty' => NULL,
                'modality' => NULL,
            ],
            [
                'id' => 4,
                'concept' => 'Licencia de paternidad',
                'accounting_account' => '0524',
                'sum' => 1,
                'state' => 1,
                'novelty' => NULL,
                'modality' => NULL,
            ],
            [
                'id' => 5,
                'concept' => 'Abandono del puesto de trabajo',
                'accounting_account' => '0506',
                'sum' => 0,
                'state' => 1,
                'novelty' => NULL,
                'modality' => NULL,
            ],
            [
                'id' => 6,
                'concept' => 'Licencia remunerada',
                'accounting_account' => '0506',
                'sum' => 1,
                'state' => 1,
                'novelty' => NULL,
                'modality' => NULL,
            ],
            [
                'id' => 7,
                'concept' => 'Licencia no remunerada',
                'accounting_account' => '0506',
                'sum' => 0,
                'state' => 1,
                'novelty' => NULL,
                'modality' => NULL,
            ],
            [
                'id' => 8,
                'concept' => 'Suspensión',
                'accounting_account' => '0506',
                'sum' => 0,
                'state' => 1,
                'novelty' => NULL,
                'modality' => NULL,
            ],
            [
                'id' => 9,
                'concept' => 'Vacaciones',
                'accounting_account' => '0539',
                'sum' => 1,
                'state' => 1,
                'novelty' => NULL,
                'modality' => NULL,
            ],
            [
                'id' => 10,
                'concept' => 'Incapacidad ARL',
                'accounting_account' => '',
                'sum' => 0,
                'state' => 1,
                'novelty' => 'INCAPACIDAD ARL',
                'modality' => 'Por Dia',
            ],
        ];

        // Añadir los timestamps
        foreach ($data as &$item) {
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        // Insertar los datos en la tabla DisabilityLeave
        DisabilityLeave::insert($data);
    }
}
