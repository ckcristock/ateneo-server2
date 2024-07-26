<?php

// namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContractTermWorkContractType;

class ContractTermWorkContractTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        ContractTermWorkContractType::truncate();

        $data = [
            [
                'id' => 1,
                'contract_term_id' => 4,
                'work_contract_type_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'contract_term_id' => 4,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'contract_term_id' => 4,
                'work_contract_type_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 24,
                'contract_term_id' => 6,
                'work_contract_type_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 25,
                'contract_term_id' => 6,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 26,
                'contract_term_id' => 6,
                'work_contract_type_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 27,
                'contract_term_id' => 1,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 28,
                'contract_term_id' => 2,
                'work_contract_type_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 29,
                'contract_term_id' => 2,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 30,
                'contract_term_id' => 2,
                'work_contract_type_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 31,
                'contract_term_id' => 3,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 32,
                'contract_term_id' => 5,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 33,
                'contract_term_id' => 7,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 34,
                'contract_term_id' => 8,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 35,
                'contract_term_id' => 9,
                'work_contract_type_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Inserta los datos en la tabla ContractTermWorkContractType
        ContractTermWorkContractType::insert($data);
    }
}
