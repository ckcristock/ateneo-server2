<?php

namespace App\Http\Controllers;

use App\Models\TypeExpense;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TypeExpenseController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(TypeExpense::get(['name As text', 'id As value']));
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
            $typeExpense = TypeExpense::create($request->all());
            return $this->success(['message' => 'Tipo de gasto creado correctamente', 'model' => $typeExpense]);
            // return response()->json('Sede creada correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeExpense  $typeExpense
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, TypeExpense $typeExpense)
    {
        try {
            $typeExpense = TypeExpense::find(request()->get('id'));
            $typeExpense->update(request()->all());
            return $this->success('Tipo de gasto actualizado correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeExpense  $typeExpense
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $typeExpense = TypeExpense::findOrFail($id);
            $typeExpense->delete();
            return $this->success('Tipo de gasto eliminado correctamente', 204);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
