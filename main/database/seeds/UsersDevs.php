<?php

use App\Models\ElectronicPayrollPerson;
use App\Models\Person;
use App\Models\Usuario;
use App\Models\WorkContract;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersDevs extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        ElectronicPayrollPerson::truncate();
        Person::truncate();
        Usuario::truncate();
        WorkContract::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $brayanDavila = Person::create([
            'identifier' => 1098781722,
            'type_document_id' => 1,
            'first_name' => 'Brayan',
            'first_surname' => 'Dávila',
            'full_name' => 'Brayan Davila',
            'birth_date' => '1995-12-18',
            'blood_type' => 'o+',
            'phone' => '3203636390',
            'email' => 'brayan.davila@innovating.com.co',
            'address' => 'Bucaramanga',
            'title' => 'Desarrollador de software',
            'eps_id' => 1,
            'compensation_fund_id' => 2,
            'degree' => 'Especialización',
            'number_of_children' => 0,
            'people_type_id' => 2,
            'severance_fund_id' => 2,
            'pension_fund_id' => 1,
            'arl_id' => 1,
            'sex' => 'masculino',
            'status' => 'activo',
            'department_id' => 11,
            'municipality_id' => 612,
            'to_globo' => 0,
            'can_schedule' => 0,
            'cell_phone' => '3203636390',
            'payroll_risks_arl_id' => 1,
            'company_worked_id' => 30,
            'dispensing_point_id' => 1,
            'place_of_birth' => 'Málaga',
            'gener' => 'Masculino',
            'visa' => 'No',
            'marital_status' => 'Soltero(a)',
            'folder_id' => 5,
        ]);

        $adrianaEspinoza = Person::create([
            'identifier' => 1002693172,
            'type_document_id' => 1,
            'first_name' => 'Adriana',
            'first_surname' => 'Espinoza',
            'full_name' => 'Adriana Espinoza',
            'birth_date' => '2000-01-01',
            'blood_type' => 'o+',
            'phone' => '3000000000',
            'email' => 'adriana.espinoza@innovating.com.co',
            'address' => 'Colombia',
            'title' => 'Desarrolladora de software',
            'eps_id' => 1,
            'compensation_fund_id' => 2,
            'degree' => 'Desarrolladora de software',
            'number_of_children' => 0,
            'people_type_id' => 2,
            'severance_fund_id' => 2,
            'pension_fund_id' => 1,
            'arl_id' => 1,
            'sex' => 'femenino',
            'status' => 'activo',
            'department_id' => 11,
            'municipality_id' => 612,
            'to_globo' => 0,
            'can_schedule' => 0,
            'cell_phone' => '3000000000',
            'payroll_risks_arl_id' => 1,
            'company_worked_id' => 30,
            'dispensing_point_id' => 1,
            'place_of_birth' => 'Colombia',
            'gener' => 'Femenino',
            'visa' => 'No',
            'marital_status' => 'Soltero(a)',
            'folder_id' => 5,
        ]);

        $carlosGuzman = Person::create([
            'identifier' => 1225091253,
            'type_document_id' => 1,
            'first_name' => 'Carlos',
            'first_surname' => 'Guzmán',
            'full_name' => 'Carlos Guzmán',
            'birth_date' => '2000-01-01',
            'blood_type' => 'o+',
            'phone' => '3000000000',
            'email' => 'carlos.guzman@innovating.com.co',
            'address' => 'Colombia',
            'title' => 'Desarrollador de software',
            'eps_id' => 1,
            'compensation_fund_id' => 2,
            'degree' => 'Desarrollador de software',
            'number_of_children' => 0,
            'people_type_id' => 2,
            'severance_fund_id' => 2,
            'pension_fund_id' => 1,
            'arl_id' => 1,
            'sex' => 'masculino',
            'status' => 'activo',
            'department_id' => 11,
            'municipality_id' => 612,
            'to_globo' => 0,
            'can_schedule' => 0,
            'cell_phone' => '3000000000',
            'payroll_risks_arl_id' => 1,
            'company_worked_id' => 30,
            'dispensing_point_id' => 1,
            'place_of_birth' => 'Colombia',
            'gener' => 'Masculino',
            'visa' => 'No',
            'marital_status' => 'Soltero(a)',
            'folder_id' => 5,
        ]);

        $nicolasGalvan = Person::create([
            'identifier' => 1095842303,
            'type_document_id' => 1,
            'first_name' => 'Nicolas',
            'first_surname' => 'Galvan',
            'full_name' => 'Nicolas Galvan',
            'birth_date' => '2000-01-01',
            'blood_type' => 'o+',
            'phone' => '3000000000',
            'email' => 'nicolas.galvan@innovating.com.co',
            'address' => 'Colombia',
            'title' => 'Desarrollador de software',
            'eps_id' => 1,
            'compensation_fund_id' => 2,
            'degree' => 'Desarrollador de software',
            'number_of_children' => 0,
            'people_type_id' => 2,
            'severance_fund_id' => 2,
            'pension_fund_id' => 1,
            'arl_id' => 1,
            'sex' => 'masculino',
            'status' => 'activo',
            'department_id' => 11,
            'municipality_id' => 612,
            'to_globo' => 0,
            'can_schedule' => 0,
            'cell_phone' => '3000000000',
            'payroll_risks_arl_id' => 1,
            'company_worked_id' => 30,
            'dispensing_point_id' => 1,
            'place_of_birth' => 'Colombia',
            'gener' => 'Masculino',
            'visa' => 'No',
            'marital_status' => 'Soltero(a)',
            'folder_id' => 5,
        ]);

        $cristianMontoya = Person::create([
            'identifier' => 1096208709,
            'type_document_id' => 1,
            'first_name' => 'Cristian',
            'first_surname' => 'Montoya',
            'full_name' => 'Cristian Montoya',
            'birth_date' => '2000-01-01',
            'blood_type' => 'o+',
            'phone' => '3000000000',
            'email' => 'cristian.montoya@innovating.com.co',
            'address' => 'Colombia',
            'title' => 'Desarrollador de software',
            'eps_id' => 1,
            'compensation_fund_id' => 2,
            'degree' => 'Desarrollador de software',
            'number_of_children' => 0,
            'people_type_id' => 2,
            'severance_fund_id' => 2,
            'pension_fund_id' => 1,
            'arl_id' => 1,
            'sex' => 'masculino',
            'status' => 'activo',
            'department_id' => 11,
            'municipality_id' => 612,
            'to_globo' => 0,
            'can_schedule' => 0,
            'cell_phone' => '3000000000',
            'payroll_risks_arl_id' => 1,
            'company_worked_id' => 30,
            'dispensing_point_id' => 1,
            'place_of_birth' => 'Colombia',
            'gener' => 'Masculino',
            'visa' => 'No',
            'marital_status' => 'Soltero(a)',
            'folder_id' => 5,
        ]);

        Usuario::create([
            'usuario' => $brayanDavila->identifier,
            'person_id' => $brayanDavila->id,
            'password' => Hash::make($brayanDavila->identifier),
            'change_password' => 0,
            'password_updated_at' => now(),
            'state' => 'activo',
            'board_id' => 1
        ]);

        Usuario::create([
            'usuario' => $adrianaEspinoza->identifier,
            'person_id' => $adrianaEspinoza->id,
            'password' => Hash::make($adrianaEspinoza->identifier),
            'change_password' => 0,
            'password_updated_at' => now(),
            'state' => 'activo',
            'board_id' => 1
        ]);

        Usuario::create([
            'usuario' => $carlosGuzman->identifier,
            'person_id' => $carlosGuzman->id,
            'password' => Hash::make($carlosGuzman->identifier),
            'change_password' => 0,
            'password_updated_at' => now(),
            'state' => 'activo',
            'board_id' => 1
        ]);

        Usuario::create([
            'usuario' => $nicolasGalvan->identifier,
            'person_id' => $nicolasGalvan->id,
            'password' => Hash::make($nicolasGalvan->identifier),
            'change_password' => 0,
            'password_updated_at' => now(),
            'state' => 'activo',
            'board_id' => 1
        ]);

        Usuario::create([
            'usuario' => $cristianMontoya->identifier,
            'person_id' => $cristianMontoya->id,
            'password' => Hash::make($cristianMontoya->identifier),
            'change_password' => 0,
            'password_updated_at' => now(),
            'state' => 'activo',
            'board_id' => 1
        ]);

        WorkContract::create([
            'position_id' => 23,
            'company_id' => 30,
            'liquidated' => 0,
            'person_id' => $brayanDavila->id,
            'salary' => 10000000.00,
            'turn_type' => 'fijo',
            'fixed_turn_id' => 1,
            'date_of_admission' => '2024-01-01',
            'work_contract_type_id' => 1,
            'contract_term_id' => 4
        ]);

        WorkContract::create([
            'position_id' => 23,
            'company_id' => 30,
            'liquidated' => 0,
            'person_id' => $adrianaEspinoza->id,
            'salary' => 10000000.00,
            'turn_type' => 'fijo',
            'fixed_turn_id' => 1,
            'date_of_admission' => '2024-01-01',
            'work_contract_type_id' => 1,
            'contract_term_id' => 4
        ]);

        WorkContract::create([
            'position_id' => 23,
            'company_id' => 30,
            'liquidated' => 0,
            'person_id' => $carlosGuzman->id,
            'salary' => 10000000.00,
            'turn_type' => 'fijo',
            'fixed_turn_id' => 1,
            'date_of_admission' => '2024-01-01',
            'work_contract_type_id' => 1,
            'contract_term_id' => 4
        ]);

        WorkContract::create([
            'position_id' => 23,
            'company_id' => 30,
            'liquidated' => 0,
            'person_id' => $nicolasGalvan->id,
            'salary' => 10000000.00,
            'turn_type' => 'fijo',
            'fixed_turn_id' => 1,
            'date_of_admission' => '2024-01-01',
            'work_contract_type_id' => 1,
            'contract_term_id' => 4
        ]);

        WorkContract::create([
            'position_id' => 23,
            'company_id' => 30,
            'liquidated' => 0,
            'person_id' => $cristianMontoya->id,
            'salary' => 10000000.00,
            'turn_type' => 'fijo',
            'fixed_turn_id' => 1,
            'date_of_admission' => '2024-01-01',
            'work_contract_type_id' => 1,
            'contract_term_id' => 4
        ]);
    }
}
