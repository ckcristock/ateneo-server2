<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyConfiguration;
use App\Models\Company;

class CompanyConfigurationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $companies = Company::all();

        // Truncar la tabla CompanyConfiguration
        CompanyConfiguration::truncate();

        // Insertar los nuevos datos
        $configurations = [
            [
                'company_id' => 1,
                'max_memos_per_employee' => 3,
                'attention_expiry_days' => 60,
                'max_item_remision' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los nuevos datos en la tabla CompanyConfiguration
        // CompanyConfiguration::insert($data);

        foreach ($companies as $company) {
            foreach ($configurations as $configuration) {
                $configurationData = new CompanyConfiguration($configuration);
                $configurationData->company_id = $company->id;
                $configurationData->save();
            }
        }

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
