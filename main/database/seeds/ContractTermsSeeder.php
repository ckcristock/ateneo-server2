<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContractTerm;

class ContractTermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        ContractTerm::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'Contratista',
                'status' => 'activo',
                'conclude' => 0,
                'modified' => 0,
                'description' => 'Contratista',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Pensionado',
                'status' => 'activo',
                'conclude' => 1,
                'modified' => 0,
                'description' => 'Pensionado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Aprendiz SENA en etapa lectiva',
                'status' => 'activo',
                'conclude' => 0,
                'modified' => 0,
                'description' => 'Aprendiz SENA en etapa lectiva',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Empleado',
                'status' => 'activo',
                'conclude' => 0,
                'modified' => 0,
                'description' => 'Empleado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Aprendiz SENA en etapa productiva',
                'status' => 'activo',
                'conclude' => 0,
                'modified' => 0,
                'description' => 'Aprendiz SENA en etapa productiva',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Tiempo Parcial',
                'status' => 'activo',
                'conclude' => 0,
                'modified' => 0,
                'description' => 'Tiempo Parcial',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Aprendiz Universitario',
                'status' => 'activo',
                'conclude' => 0,
                'modified' => 0,
                'description' => 'Aprendiz Universitario',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Estudiante (ley 789 de 2002)',
                'status' => 'activo',
                'conclude' => 0,
                'modified' => 0,
                'description' => 'Estudiante (ley 789 de 2002)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Estudiante (decreto 055)',
                'status' => 'activo',
                'conclude' => 0,
                'modified' => 0,
                'description' => 'Estudiante (decreto 055)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Inserta los datos en la tabla ContractTerm
        ContractTerm::insert($data);
    }
}
