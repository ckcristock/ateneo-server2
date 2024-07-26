<?php

namespace App\Http\Controllers;

use App\Models\ProductAccountingPlan;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Traits\ApiResponser;

class ProductAccountingPlanController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        //
        $companyId = $request->get('company_id');
        $tipoCatalogo = $request->get('Tipo_Catalogo');
        $data = ProductService::getProductsAccountPlans($tipoCatalogo, $companyId);
        return $this->success($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //
        try {
            //code...
            ProductAccountingPlan::updateOrCreate(["id" => $request->get('product_accounting_plan_id')], $request->all());
            return $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->errorResponse('ha ocurrido un error' . $th->getMessage());
            //throw $th;
        }
    }
}
