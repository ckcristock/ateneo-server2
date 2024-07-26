<?php

namespace App\Http\Controllers;

use App\Models\CompanyConfiguration;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyConfigurationController extends Controller
{
    use ApiResponser;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'company_id' => [
                    'required',
                    'integer',
                    Rule::exists('companies', 'id'),
                    Rule::unique('company_configurations', 'company_id'),
                ],
                'max_memos_per_employee' => 'required|integer|min:1',
                'attention_expiry_days' => 'required|integer|min:1',
                'max_item_remision' => 'required|integer|min:1',
            ]);

            CompanyConfiguration::create([
                'company_id' => $validatedData['company_id'],
                'max_memos_per_employee' => $validatedData['max_memos_per_employee'],
                'attention_expiry_days' => $validatedData['attention_expiry_days'],
                'max_item_remision' => $validatedData['max_item_remision'],
            ]);

            return $this->success('Configuración de la empresa creada exitosamente');
        } catch (\Throwable $th) {
            return $this->error(
                $th->getMessage(),
                $th->getCode() ?: 500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'max_memos_per_employee' => 'integer|min:1',
                'attention_expiry_days' => 'integer|min:1',
                'max_item_remision' => 'integer|min:1',
            ]);

            $config = CompanyConfiguration::findOrFail($id);

            if (isset($data['max_memos_per_employee'])) {
                $config->max_memos_per_employee = $data['max_memos_per_employee'];
            }

            if (isset($data['attention_expiry_days'])) {
                $config->attention_expiry_days = $data['attention_expiry_days'];
            }

            if (isset($data['max_item_remision'])) {
                $config->max_item_remision = $data['max_item_remision'];
            }

            $config->save();

            return $this->success('Company configuration updated successfully');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        $configuration = CompanyConfiguration::where('company_id', $id)->first();
        return $this->success($configuration); 
        
    }
}
