<?php

// namespace Database\Seeders;

use App\Models\Company;
use App\Models\CountableIncome;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountableIncomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $companies = Company::all();
        // Reiniciar el contador de IDs
        CountableIncome::truncate();

        $incomes = [
            [
                'concept' => 'Comisiones',
                'type' => 'constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Bonificación prestacional',
                'type' => 'constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Otros ingresos',
                'type' => 'constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Auxilio de movilización',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Honorarios',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Otros ingresos no prestacionales',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Auxilio de Alimentación',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Bonificación no prestacional',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Bonificación extralegal por desempeño',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Bono canasta',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Bonificación a mera liberalidad',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Auxilio de Salud',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
            [
                'concept' => 'Auxilio de Estudio',
                'type' => 'no constitutivo',
                'state' => 1,
                'editable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                // 'account_plan_id' => 0,
                'company_id' => 0,
            ],
        ];

        // Inserta los datos en la tabla CountableIncome
        // CountableIncome::insert($data);
        foreach ($companies as $company) {
            foreach ($incomes as $income) {
                $acountableIncome = new CountableIncome($income);
                $acountableIncome->company_id = $company->id;
                $acountableIncome->save();
            }
        }
    }
}
