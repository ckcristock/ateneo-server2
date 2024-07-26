<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TypeAppointment;

class TypeAppointmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Desactivar temporalmente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar la tabla TypeAppointment
        TypeAppointment::truncate();

        // Insertar los nuevos datos
        $data = [
            [
                'description' => 'TELEMEDICINA',
                'id' => 1,
                'name' => 'TELEMEDICINA',
                'icon' => 'fa fa-video',
                'face_to_face' => 0,
                'ips' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'PRESENCIAL',
                'id' => 2,
                'name' => 'PRESENCIAL',
                'icon' => 'fa fa-user',
                'face_to_face' => 1,
                'ips' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los nuevos datos en la tabla TypeAppointment
        TypeAppointment::insert($data);

        // Activar nuevamente las restricciones de clave externa
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
