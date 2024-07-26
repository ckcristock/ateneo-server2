<?php

namespace App\Http\Controllers;

use App\Models\Variable;
use App\Http\Requests\StoreVariableRequest;
use App\Http\Requests\UpdateVariableRequest;

class VariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVariableRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Variable $variable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVariableRequest $request, Variable $variable)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variable $variable)
    {
        //
    }
}
