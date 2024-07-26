<?php

namespace App\Http\Controllers;

use App\Models\CompanyPaymentConfiguration;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CompanyPaymentConfigurationController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }
    /**
     * Display a listing of the resource.
     * Muestra los datos de la compañia 1
     * porque es una sola empresa
     * Cambiar si se requiere multiempresa
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->success(CompanyPaymentConfiguration::where('company_id', $request->company_id)->first());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $exist = CompanyPaymentConfiguration::where('company_id', $request->company_id)->first();
        if ($exist) {
            $differences = findDifference($request->all(), CompanyPaymentConfiguration::class);
        }
        try {
            $companyConfiguration = CompanyPaymentConfiguration::updateOrCreate(['company_id' => $request->get('company_id')], $request->all());
            if ($exist) {
                saveHistoryCompanyData($differences, CompanyPaymentConfiguration::class, $request->company_id);
            }
            return ($companyConfiguration->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }

    }
}
