<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DrivingLicense;

class DrivingLicensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        DrivingLicense::truncate();

        $data = [
            [
                'id' => 3,
                'type' => 'C1',
                'description' => 'Para la conducción de automóviles, camperos, camionetas y microbuses publicos.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'type' => 'A1',
                'description' => 'Para la conducción de motocicletas con cilindrada hasta de 125 c.c',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'type' => 'A2',
                'description' => 'Para la conducción de motocicletas, motociclos y mototriciclos con cilindrada mayor a 125 c.c.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'type' => 'B1',
                'description' => 'Para la conducción de automóviles, motocarros, cuatrimotos, camperos, camionetas y microbuses particulares.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'type' => 'B2',
                'description' => 'Para la conducción de camiones rígidos, busetas y buses particulares.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'type' => 'B3',
                'description' => 'Para la conducción de vehículos articulados particulares.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'type' => 'C2',
                'description' => 'Para la conducción de camiones rígidos, busetas y buses publicos.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'type' => 'C3',
                'description' => 'Para la conducción de vehículos articulados publicos.',
                'state' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla DrivingLicense
        DrivingLicense::insert($data);
    }
}
