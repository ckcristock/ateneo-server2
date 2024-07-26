<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\RegimenType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class RegimenTypeController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(RegimenType::get(['name As text', 'id As value']));
    }

    public function paginate(Request $request)
    {
        return $this->success(
            RegimenType::when($request->name, function ($q, $fill) {
                $q->where('name', 'like', "%$fill%");
            })
            ->when($request->code, function ($q, $fill) {
                $q->where('code', 'like', "%$fill%");
            })
            ->when($request->state, function ($q, $fill) {
                $q->where('state', $fill);
            })

            ->orderBy('state') 
            ->orderBy('name')  
            
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function regimenesConNiveles($id)
    {
        return $this->success(
            Level::where('regimen_id', $id)
            ->when(request()->get('name'), function ($q, $fill) {
                $q->where('name', 'like', '%' . $fill . '%');
            })
            ->when(request()->get('code'), function ($q, $fill) {
                $q->where('code', 'like', '%' . $fill . '%' );
            })
            ->when(request()->get('cuote'), function ($q, $fill) {
                $q->where('cuote', 'like', '%' . $fill . '%' );
            })->paginate(request()->get('pageSize', 5), ['*'], 'page', request()->get('page', 1)));
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
            RegimenType::updateOrCreate( ['id' => $request->get('id')], $request->all());
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
