<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SubTypeAppointment;

class SubTypeAppointmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla SubTypeAppointment
        SubTypeAppointment::truncate();

        // Obtener la fecha y hora actual
        $currentTimestamp = now();

        // Insertar los nuevos datos
        $data = [
            [
                'type_appointment_id' => 2,
                'description' => 'CONSULTA',
                'name' => 'CONSULTA',
                'company_owner' => 1,
                'procedure' => 0,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'type_appointment_id' => 2,
                'description' => 'PROCEDIMIENTO',
                'name' => 'PROCEDIMIENTO',
                'company_owner' => 0,
                'procedure' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'type_appointment_id' => 0,
                'description' => 'TELEORIENTACION',
                'name' => 'TELEORIENTACION',
                'company_owner' => 1,
                'procedure' => 0,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'type_appointment_id' => 1,
                'description' => 'TELECONSULTA',
                'name' => 'TELECONSULTA',
                'company_owner' => 1,
                'procedure' => 0,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
        ];

        // Insertar los nuevos datos en la tabla SubTypeAppointment
        SubTypeAppointment::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
