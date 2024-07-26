<?php

namespace App\Http\Controllers;

use App\Models\RetentionType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetentionTypeController extends Controller
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
            RetentionType::all(['name as text', 'id as value'])
        );
    }

    public function select()
    {
        return $this->success(RetentionType::select('id as value', DB::raw("name as text"))->get());
    }

    public function paginate()
    {
        return $this->success(
            RetentionType::with('accountPlan')
                ->when(request()->get('nombre'), function ($q, $fill) {
                    $q->where('r.name', 'like', '%' . $fill . '%');
                })
                ->when(request()->get('porcentaje'), function ($q, $fill) {
                    $q->where('percentage', 'like', '%' . $fill . '%');
                })
                ->when(request()->get('estado'), function ($q, $fill) {
                    $q->where('state', '=', $fill);
                })
                ->when(request()->get('type'), function ($q, $fill) {
                    $q->where('type', $fill);
                })
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
            RetentionType::updateOrCreate(['id' => $request->get('id')], $request->all());
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
