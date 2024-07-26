<?php

namespace App\Http\Controllers;

use App\Models\FiscalResponsibility;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiscalResponsibilityController extends Controller
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
            FiscalResponsibility::where('state', 'Activo')->get(['name as text', 'id as value', 'code'])
        );
    }

    public function paginate(Request $request)
    {
        return $this->success(
            DB::table('fiscal_responsibilities as f')
                ->select(
                    'f.id',
                    'f.code',
                    'f.name',
                    'f.state',
                )
                ->when($request->name, function ($q, $fill) {
                    $q->where('f.name', 'like', '%' . $fill . '%');
                })
                ->when($request->state, function ($q, $fill) {
                    $q->where('f.state', $fill);
                })

                ->orderBy('f.state') 
                ->orderBy('f.name') 

                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
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
            FiscalResponsibility::updateOrCreate(['id' => $request->get('id')], $request->all());
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
