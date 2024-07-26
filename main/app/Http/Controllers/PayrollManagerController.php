<?php

namespace App\Http\Controllers;

use App\Models\CompanyPayrollManager;
use App\Models\PayrollManager;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollManagerController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        //return $this->success(PayrollManager::with('responsable: identifier as dni, CONCAT_WS(" ", first_name, first_surname) as text')->get());
        return $this->success(PayrollManager::with([
            'responsable' => function ($q) {
                $q->select(
                    'id',
                    'identifier',
                    DB::raw('CONCAT_WS(" ",first_name,first_surname) as text')
                );
            }
        ])->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            if ($request->payroll_manager_id) {
                $companyPayrollManager = CompanyPayrollManager::where([
                    ['payroll_manager_id', '=', $request->payroll_manager_id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();

                if ($companyPayrollManager) {
                    $companyPayrollManager->update([
                        'person_id' => $request->person_id,
                    ]);
                    return $this->success('Actualizado con éxito');
                } else {
                    CompanyPayrollManager::create([
                        'payroll_manager_id' => $request->payroll_manager_id,
                        'person_id' => $request->person_id,
                        'company_id' => $this->getCompany(),
                    ]);
                    return $this->success('Asignado con éxito');
                }
            } else {
                $payrollManager = PayrollManager::create([
                    'area' => $request->area,
                ]);
                CompanyPayrollManager::create([
                    'payroll_manager_id' => $payrollManager->id,
                    'person_id' => $request->person_id,
                    'company_id' => $this->getCompany(),
                ]);
                return $this->success('Creado con éxito');
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }
}
