<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Models\VariableType;
use App\Http\Requests\StoreVariableTypeRequest;
use App\Http\Requests\UpdateVariableTypeRequest;

class VariableTypeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function listarVariableTypes() 
    {
        $variableTypes = VariableType::with('conditions')->get();
        return $this->success($variableTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVariableTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(VariableType $variableType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVariableTypeRequest $request, VariableType $variableType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VariableType $variableType)
    {
        //
    }
}
