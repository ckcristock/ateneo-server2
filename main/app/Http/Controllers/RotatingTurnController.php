<?php

namespace App\Http\Controllers;

use App\Models\RotatingTurn;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Throwable;

class RotatingTurnController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function index()
    {
        //
        return $this->success(
            RotatingTurn::where('company_id', $this->getCompany())
                ->where('state', 'Activo')
                ->with('sunday')
                ->with('saturday')
                ->when(request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->get(["*", "id as value", "name as text", "state"])
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function paginate(Request $request)
    {
        return $this->success(
            RotatingTurn::orderBy('state')
                ->where('company_id', $this->getCompany())
                ->select("*", "id as value", "name as text", "state")
                ->with('sunday', 'saturday')
                ->when(request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->when($request->company_id, function ($q, $fill) {
                    $q->where('company_id', "%$fill%");
                })
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function create()
    {
        //
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
            $rotatingTurnData = $request->all();
            $rotatingTurnData['company_id'] = $this->getCompany();
            return $this->success(RotatingTurn::create($rotatingTurnData));
            /* return $this->success(RotatingTurn::create($request->all())); */
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        //
        return $this->success(RotatingTurn::find($id));
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
        //
        try {

            $rt = RotatingTurn::find($id);
            $rotatingTurnData = $request->all();
            $rotatingTurnData['company_id'] = $this->getCompany();
            //return [ "antes" => $request->all(), "despues" => $rotatingTurnData];
            $rt->update($rotatingTurnData);
            return $this->success('Actualizado correctamente');
        } catch (Throwable $th) {
            return $this->error($th->getMessage(), 402);
        }
    }
    
    public function changeState($id)
    {
        $turno = RotatingTurn::find($id);

        $turno->state = $turno->state == "activo" ? "inactivo" : "activo";
        $turno->save();
        return $this->success(
            "Turno Actualizado Correctamente",
        );
    }
}
