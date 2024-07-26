<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VisaType;

class VisaTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        VisaType::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'B1',
                'purpose' => 'Negocios.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'B2',
                'purpose' => 'Turismo.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'H-1B',
                'purpose' => 'Ocupación especializada.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'H-1B1',
                'purpose' => 'Visa de trabajo temporal.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'H-2A',
                'purpose' => 'Trabajadores agricolas estacionales.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'H-2B',
                'purpose' => 'Trabajadores calificados y no calificados.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'H-3',
                'purpose' => 'Entrenamiento.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'L-1',
                'purpose' => 'Tranferencia dentro de una compañía.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'O',
                'purpose' => 'Habilidad extraordinaria.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla VisaType
        VisaType::insert($data);
    }
}
