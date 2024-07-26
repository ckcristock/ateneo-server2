<?php

namespace App\Http\Controllers;

use App\Models\WorkContractType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\ResponseTrait;

class WorkContractTypeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(
            WorkContractType::with('contractTerms')->get()
        );
    }

    public function getWorkContractTypeList()
    {
        return $this->success(
            WorkContractType::get(['id as value', 'name as text'])
        );
    }

    public function paginate()
    {
        return $this->success(
            WorkContractType::with('contractTerms')
                ->when(Request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->paginate(Request()->get('pageSize', 5), ['*'], 'page', Request()->get('page', 1))
        );
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
            $typeContract = WorkContractType::updateOrCreate(['id' => $request->get('id')], $request->all());
            /* $typeContract->contractTerms()->detach();
            $typeContract->contractTerms()->attach($request->contract_terms); */
            return ($typeContract->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
