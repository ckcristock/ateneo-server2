<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PayrollSocialSecurityCompany;
use Illuminate\Support\Facades\DB;

class PayrollSocialSecurityCompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla CompanyPayrollSocialSecurityCompany
        PayrollSocialSecurityCompany::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'prefix' => 'salud',
                'concept' => 'Salud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'pension',
                'concept' => 'Pensión',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los nuevos datos en la tabla PayrollSocialSecurityCompany
        PayrollSocialSecurityCompany::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
