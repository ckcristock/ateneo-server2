<?php

// namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CountableDeduction;
use Illuminate\Support\Facades\DB;

class CountableDeductionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $companies = Company::all();

        // Truncar la tabla CountableDeduction
        CountableDeduction::truncate();

        // Obtener la fecha y hora actual
        $currentTimestamp = now();

        // Insertar los nuevos datos
        $countableDeductions = [
            [
                'concept' => 'Préstamo',
                'state' => 1,
                'editable' => 0,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Celular',
                'state' => 1,
                'editable' => 0,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Otras deducciones',
                'state' => 1,
                'editable' => 0,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Crédito libranza',
                'state' => 1,
                'editable' => 0,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Aportes voluntarios a pensión',
                'state' => 1,
                'editable' => 0,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
        ];

        // Insertar los nuevos datos en la tabla CountableDeduction
        // CountableDeduction::insert($data);

        foreach ($companies as $company) {
            foreach ($countableDeductions as $deduction) {
                $countableDeduction = new CountableDeduction($deduction);
                $countableDeduction->company_id = $company->id;
                $countableDeduction->save();
            }
        }

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
