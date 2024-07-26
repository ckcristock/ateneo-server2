<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class StoretemplateRequest extends FormRequest
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
            'template.name' => 'required|string|max:255',
            'template.specialities' => 'required|array',
            'template.specialities.*' => 'integer|exists:specialities,id',
            'secciones' => 'required|array|min:1',
            'secciones.*.name' => 'required|string|max:255',
            'secciones.*.variables' => 'required|array|min:1',
            'secciones.*.variables.*.id_variable_type' => 'required|integer|exists:variable_types,id',
            'secciones.*.variables.*.name' => 'required|string|max:255',
            'secciones.*.variables.*.size' => 'nullable|string|max:255',
            'secciones.*.variables.*.required' => 'boolean',
            'secciones.*.variables.*.options' => 'nullable|array',
            'secciones.*.variables.*.options.*.name' => 'required_with:secciones.*.variables.*.options|string|max:255', 
            'secciones.*.variables.*.list' => 'nullable|array', // Nuevo campo list
            'secciones.*.variables.*.list.name' => 'required_with:secciones.*.variables.*.list|string|max:255', // Nombre de la lista requerido
            'secciones.*.variables.*.list.options' => 'required_with:secciones.*.variables.*.list|array', // Opciones requeridas
            'secciones.*.variables.*.list.options.*.name' => 'required|string|max:255', // Nombre de las opciones requerido 
            'secciones.*.variables.*.conditions' => 'nullable|array',
            'secciones.*.variables.*.conditions.*.id_condition' => 'required|integer|exists:type_conditions,id',
            'secciones.*.variables.*.conditions.*.value' => 'required|string|max:255',
            'secciones.*.variables.*.rules' => 'nullable|array', // Regla para verificar si existe la clave 'rules'
            'secciones.*.variables.*.rules.*.operator' => 'required|integer',
            'secciones.*.variables.*.rules.*.value' => 'string|max:255',
            'secciones.*.variables.*.rules.*.conditions' => 'nullable|array',
            'secciones.*.variables.*.rules.*.conditions.*.operator' => 'required|integer',
            'secciones.*.variables.*.rules.*.conditions.*.variable' => 'required|string', // Reemplaza por la validación adecuada según tus necesidades
            'secciones.*.variables.*.rules.*.conditions.*.value' => 'string|max:255',
            'secciones.*.variables.*.rules.*.conditions.*.logicalOperator' => 'required|string|in:and,or',
            'secciones.*.variables.*.rules.*.variable' => 'required|array', // Regla para verificar si existe la clave 'variable'
            'secciones.*.variables.*.rules.*.variable.id_variable_type' => 'required|integer|exists:variable_types,id',
            'secciones.*.variables.*.rules.*.variable.name' => 'required|string|max:255',
            'secciones.*.variables.*.rules.*.variable.size' => 'nullable|string|max:255',
            'secciones.*.variables.*.rules.*.variable.required' => 'required|boolean',
            'secciones.*.variables.*.rules.*.variable.conditions' => 'nullable|array',
            'secciones.*.variables.*.rules.*.variable.conditions.*.id_condition' => 'required|integer|exists:type_conditions,id',
            'secciones.*.variables.*.rules.*.variable.conditions.*.value' => 'required|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
