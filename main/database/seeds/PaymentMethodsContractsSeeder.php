<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethodContract;

class PaymentMethodsContractsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        PaymentMethodContract::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'Paquete/Canasta/Conjunto integral en salud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Grupos relacionados por diagnóstico',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Integral por grupo de riesgo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Pago por contrato por especialidad',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Pago por escenario de atención',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Pago por tipo de servicio',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Pago global prospectivo por episodio',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Pago global prospectivo por grupo de riesgo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Pago global prospectivo por especialidad',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Pago global prospectivo por nivel de complejidad',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'name' => 'Capitación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'name' => 'Por servicio',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Inserta los datos en la tabla PaymentMethodContract
        PaymentMethodContract::insert($data);
    }
}
