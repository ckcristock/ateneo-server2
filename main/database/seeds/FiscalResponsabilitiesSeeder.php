<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FiscalResponsabilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlFile = database_path('seeds/sql/fiscalResponsabilities.sql');
        
        // Verifica si el archivo existe
        if (file_exists($sqlFile)) {
            // Ejecuta el contenido del archivo SQL
            DB::unprepared(file_get_contents($sqlFile));
        } else {
            $this->command->error("El archivo '$sqlFile' no existe.");
        }
    }

}
