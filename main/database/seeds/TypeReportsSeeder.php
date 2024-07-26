<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeReport;

class TypeReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        TypeReport::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'Reporte de atenciones',
                'show_input' => 0,
                'created_at' => now(),
                'update_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Reporte de agendas',
                'show_input' => 0,
                'created_at' => now(),
                'update_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Reporte de estado de agendas',
                'show_input' => 0,
                'created_at' => now(),
                'update_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Reporte de lista de espera',
                'show_input' => 0,
                'created_at' => now(),
                'update_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Reporte citas Futuras',
                'show_input' => 0,
                'created_at' => now(),
                'update_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Por Paciente',
                'show_input' => 1,
                'created_at' => now(),
                'update_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Por agente',
                'show_input' => 1,
                'created_at' => now(),
                'update_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla TypeReport
        TypeReport::insert($data);
    }
}
