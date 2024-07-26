<?php

namespace App\Http\Controllers;

use App\Models\TypeOutcome;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TypeOutcomeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(TypeOutCome::get(['name As text', 'id As value']));
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
            $typeOutcome = TypeOutcome::create($request->all());
            return $this->success(['message' => 'Tipo de egreso creado correctamente', 'model' => $typeOutcome]);
            // return response()->json('Sede creada correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeOutcome  $typeOutcome
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, TypeOutcome $typeOutcome)
    {
        try {
            $typeOutcome = TypeOutcome::find(request()->get('id'));
            $typeOutcome->update(request()->all());
            return $this->success('Tipo de egreso actualizado correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeOutcome  $typeOutcome
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $typeOutcome = TypeOutcome::findOrFail($id);
            $typeOutcome->delete();
            return $this->success('Tipo de egreso eliminado correctamente', 204);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
