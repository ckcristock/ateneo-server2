<?php

namespace App\Http\Controllers;

use App\Models\TypeGlossy;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TypeGlossyController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(TypeGlossy::get(['name As text', 'id As value']));
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
            $typeGlossy = TypeGlossy::create($request->all());
            return $this->success(['message' => 'Tipo de glosa creado correctamente', 'model' => $typeGlossy]);
            // return response()->json('Sede creada correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeGlossy  $typeGlossy
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, TypeGlossy $typeGlossy)
    {
        try {
            $typeGlossy = TypeGlossy::find(request()->get('id'));
            $typeGlossy->update(request()->all());
            return $this->success('Tipo de glosa actualizado correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeGlossy  $typeGlossy
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $typeGlossy = TypeGlossy::findOrFail($id);
            $typeGlossy->delete();
            return $this->success('Tipo de glosa eliminado correctamente', 204);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
