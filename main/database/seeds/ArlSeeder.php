<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Arl;

class ArlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        Arl::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'Aseguradora de Vida Colseguros',
                'nit' => '860027404-16',
                'code' => '36905',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 2,
                'name' => 'Seguros de Vida Alfa S.A.',
                'nit' => '860031979-8',
                'code' => '14-17',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 3,
                'name' => 'Liberty Seguros de Vida',
                'nit' => '860039988-0',
                'code' => '14-18',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 4,
                'name' => 'Seguros de Vida del Estado S.A.',
                'nit' => '860009578-6',
                'code' => '14-19',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 5,
                'name' => 'La Previsora Vida S.A. Compañia de Seguros',
                'nit' => '860002400-2',
                'code' => '14-23',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 11,
                'name' => 'Riesgos Profesionales Colmena S.A Compañia de Seguros de Vida',
                'nit' => '800226175-3',
                'code' => '14-25',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 12,
                'name' => 'BBVA Seguros de Vida Colombia S.A',
                'nit' => '800240882-0',
                'code' => '13-41',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 14,
                'name' => 'COMPAÑIA SURAMERICANA DE SEGUROS S.A.',
                'nit' => '890903407-9',
                'code' => '13-18',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 15,
                'name' => 'La Equidad Seguros de Vida Organismo Cooperativo - La Equidad Vida',
                'nit' => '860028415-5',
                'code' => '14-29',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 16,
                'name' => 'SEGUROS DE VIDA COLPATRIA',
                'nit' => '860002183-9',
                'code' => '36995',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 18,
                'name' => 'CIA. de Seguros Bolivar S.A.',
                'nit' => '860002503-2',
                'code' => '14-7',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
            [
                'id' => 19,
                'name' => 'Compañía de Seguros de Vida Aurora',
                'nit' => '860002137-5',
                'code' => '14-8',
                'editable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'Activo',
            ],
        ];

        // Inserta los datos en la tabla Arl
        Arl::insert($data);
    }
}
