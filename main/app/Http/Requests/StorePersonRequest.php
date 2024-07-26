<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "person.identifier" => ["required", "integer", "unique:people,identifier"],
            "person.type_document_id" => ["required", "integer", "exists:document_types,id"],
            "person.first_name" => ["required", "string", "max:191"],
            "person.second_name" => ["nullable", "string", "max:191"],
            "person.first_surname" => ["required", "string", "max:255"],
            "person.second_surname" => ["nullable", "string", "max:191"],
            "person.birth_date" => ["required", "date"],
            "person.blood_type" => [
                "required",
                Rule::in([
                    'a+',
                    'A+',
                    'a-',
                    'A-',
                    'b+',
                    'B+',
                    'b-',
                    'B-',
                    'ab+',
                    'AB+',
                    'ab-',
                    'AB-',
                    'o+',
                    'O+',
                    'o-',
                    'O-'
                ])
            ],
            "person.phone" => ["nullable", "string"],
            "person.email" => ["required", "string", "email", "max:191"],
            "person.address" => ["required", "string", "max:255"],
            "person.title" => ["required", "string", "max:191"],
            "person.image" => ["required", "string"],
            "person.eps_id" => ["required", "integer", "exists:eps,id"],
            "person.compensation_fund_id" => ["required", "integer", "exists:compensation_funds,id"],
            "person.degree" => ["required", "string", "max:191"],
            "person.number_of_children" => ["required", "integer", "min:0", "max:100"],
            "person.severance_fund_id" => ["required", "integer", "exists:severance_funds,id"],
            "person.shirt_size" => ["required", "string", "max:255"],
            "person.shue_size" => ["required", "string", "max:255"],
            "person.pension_fund_id" => ["required", "integer", "exists:pension_funds,id"],
            "person.arl_id" => ["required", "integer", "exists:arl,id"],
            "person.pants_size" => ["required", "string", "max:255"],
            "person.signature" => ["nullable", "string"],
            "person.cell_phone" => ["required", "string", "max:12"],
            "person.place_of_birth" => ["required", "string"],
            "person.gener" => ["required", Rule::in(['Femenino', 'Masculino'])],
            "person.visa" => ["nullable", Rule::in(['Si', 'No'])],
            "person.passport_number" => ["nullable", "string", "max:255"],
            "person.marital_status" => ["required", Rule::in(['Soltero(a)', 'Casado(a)', 'Divorciado(a)', 'Viudo(a)', 'Unión Libre'])],

            "person.workContract.company_id" => ["required", "integer", "exists:companies,id"],
            "person.workContract.group_id" => ["required", "integer", "exists:groups,id"],
            "person.workContract.dependency_id" => ["required", "integer", "exists:dependencies,id"],
            "person.workContract.position_id" => ["required", "integer", "exists:positions,id"],
            "person.workContract.salary" => ["required", "numeric", "min:0"],
            "person.workContract.date_of_admission" => ["required", "date"],
            "person.workContract.date_end" => ["nullable", "date"],
            "person.workContract.work_contract_type_id" => ["required", "integer", "exists:work_contract_types,id"],
            "person.workContract.contract_term_id" => ["required", "integer", "exists:contract_terms,id"],
            "person.workContract.turn_type" => ["required", Rule::in(['fijo', 'rotativo'])],
            "person.workContract.fixed_turn_id" => ["nullable", "integer", "exists:fixed_turns,id"],
            "person.workContract.transport_assistance" => ["nullable", "boolean"],
        ];
    }

    /**
     * Handles the case when validation fails.
     *
     * @param Validator $validator The instance of the Validator class.
     * @throws HttpResponseException If the validation fails, this exception is thrown.
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    public function attributes()
    {
        return [
            'person.image' => 'imagen',
            'person.type_document_id' => 'tipo de documento',
            'person.identifier' => 'número de documento',
            'person.first_name' => 'primer nombre',
            'person.second_name' => 'segundo nombre',
            'person.first_surname' => 'primer apellido',
            'person.second_surname' => 'segundo apellido',
            'person.email' => 'correo electrónico',
            'person.birth_date' => 'fecha de nacimiento',
            'person.place_of_birth' => 'lugar de nacimiento',
            'person.address' => 'dirección',
            'person.phone' => 'teléfono',
            'person.cell_phone' => 'celular',
            'person.gener' => 'género',
            'person.blood_type' => 'tipo de sangre',
            'person.marital_status' => 'estado civil',
            'person.number_of_children' => 'número de hijos',
            'person.degree' => 'titulo',
            'person.title' => 'grado',
            'person.passport_number' => 'número de pasaporte',
            'person.visa' => 'visa',
            'person.signature' => 'firma',
            'person.eps_id' => 'EPS',
            'person.compensation_fund_id' => 'fondo de compensación',
            'person.severance_fund_id' => 'fondo de cesantías',
            'person.pension_fund_id' => 'fondo de pensión',
            'person.arl_id' => 'ARL',
            'person.shirt_size' => 'talla camisa',
            'person.pants_size' => 'talla pantalones',
            'person.shue_size' => 'talla botas',
            'person.workContract.company_id' => 'empresa',
            'person.workContract.group_id' => 'grupo',
            'person.workContract.dependency_id' => 'dependencia',
            'person.workContract.position_id' => 'cargo',
            'person.workContract.salary' => 'salario',
            'person.workContract.date_of_admission' => 'fecha de ingreso',
            'person.workContract.date_end' => 'fecha de fin de contrato',
            'person.workContract.work_contract_type_id' => 'tipo de contrato',
            'person.workContract.contract_term_id' => 'término del contrato',
            'person.workContract.turn_type' => 'tipo de turno',
            'person.workContract.fixed_turn_id' => 'turno fijo',
            'person.workContract.transport_assistance' => 'auxilio de transporte',
        ];
    }
}
