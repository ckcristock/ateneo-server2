<?php

namespace App\Http\Controllers;

use App\Models\FixedTurn;
use App\Models\FixedTurnHour;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class FixedTurnController extends Controller
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
        /* return $this->success(FixedTurn::all(["id as value", "name as text", "state"]));; */
        return $this->success(
            FixedTurn::where('company_id', $this->getCompany())
                ->where('state', 'Activo')
                ->get(["*", "id as value", "name as text", "state"])
        );
    }

    public function paginate()
    {
        return $this->success(
            FixedTurn::where('company_id', $this->getCompany())
                ->orderBy('state')
                ->select("id as value", "name as text", "state")
                ->when(request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function activeFixedTurns()
    {
        return $this->success(
            FixedTurn::where('company_id', $this->getCompany())
                ->where('state', '=', 'Activo')
                ->select("id as value", "name as text", "state")
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $req)
    {
        try {
            $fixedTurnData = $req->except("days");
            $fixedTurnData['company_id'] = $this->getCompany();
            $fixed = FixedTurn::create($fixedTurnData);
            $hours = $req->get("days");
            $fixed->horariosTurnoFijo()->createMany($hours);
            return $this->success("Creado con éxito");

        } catch (\Throwable $err) {
            return $this->error($err->getMessage(), 500);
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
        try {
            return $this->success(
                FixedTurn::where("id", $id)
                    ->with("horariosTurnoFijo")
                    ->first()
            );
        } catch (\Throwable $err) {
            return $this->error($err->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $req, $id)
    {
        try {
            $fixedTurnData = $req->except("days");
            $fixed = FixedTurn::find($id);
            $fixedTurnData['company_id'] = $this->getCompany();
            $fixed->update($fixedTurnData);
            $hours = $req->get("days");

            FixedTurnHour::where('fixed_turn_id', $id)->delete();
            $fixed->horariosTurnoFijo()->createMany($hours);
            return $this->success("Actualizado con éxito");
        } catch (\Throwable $err) {
            return $this->error($err->getMessage(), 500);
        }
    }

    public function changeState($id)
    {
        $turno = FixedTurn::find($id);
        $turno->state = $turno->state == "activo" ? "inactivo" : "activo";
        $turno->save();
        return $this->success("Turno Actualizado Correctamente");
    }
}
