<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Responsible;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class ResponsiblesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla Responsible
        Responsible::truncate();

        $companies = Company::all();

        // Insertar los nuevos datos
        $responsibles = [
            [
                'name' => 'DIRECTOR ADMINISTRATIVO Y FINANCIERO',
                'person_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'company_id' => 1,
            ],
            [
                'name' => 'ADMINISTRADOR GENERAL (SOFTWARE)',
                'person_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'company_id' => 1,
            ],
            [
                'name' => 'RESPONSABLE DE RECURSOS HUMANOS',
                'person_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'company_id' => 1,
            ],
        ];

        // Insertar los nuevos datos en la tabla Responsible
        // Responsible::insert($data);

        foreach ($companies as $company) {
            foreach ($responsibles as $responsible) {
                $respon = new Responsible($responsible);
                $respon->company_id = $company->id;
                $respon->save();
            }
        }

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
