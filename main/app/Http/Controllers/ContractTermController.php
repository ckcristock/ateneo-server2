<?php

namespace App\Http\Controllers;

use App\Models\ContractTerm;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ContractTermController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(ContractTerm::with('workContractTypes')->get());
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
            $nuevo = ContractTerm::updateOrCreate(['id' => $request->get('id'), "status" => "activo"], $request->all());
            return ($nuevo->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode());
        }
    }

    /**
     *
     * Retorna los elementos de CotractTerm paginado y filtrado por nombre
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate()
    {
        $data = Request()->all();
        return $this->success(
            ContractTerm::with('workContractTypes')
                ->when(Request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->paginate(request()->get('pageSize', 5), ['*'], 'page', request()->get('page', 1))
        );
    }

}
