<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PayrollOvertime;

class PayrollOvertimesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        PayrollOvertime::truncate();

        $data = [
            [
                'id' => 1,
                'prefix' => 'hed',
                'concept' => 'Horas extras diurnas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'prefix' => 'hen',
                'concept' => 'Horas extras nocturnas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'prefix' => 'hrn',
                'concept' => 'Horas recargos nocturnos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'prefix' => 'heddf',
                'concept' => 'Horas extras diurnas dominicales y festivas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'prefix' => 'hrddf',
                'concept' => 'Horas recargo diurnas dominicales y festivas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'prefix' => 'hendf',
                'concept' => 'Horas extras nocturnas dominicales y festivas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'prefix' => 'hrndf',
                'concept' => 'Horas recargo nocturas dominicales y festivas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla PayrollOvertime
        PayrollOvertime::insert($data);
    }
}
