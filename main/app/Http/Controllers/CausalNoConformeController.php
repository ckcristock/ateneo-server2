<?php

namespace App\Http\Controllers;

use App\Models\CausalNoConforme;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

class CausalNoConformeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lista = CausalNoConforme::paginate($request->pageSize ?? 10);
        return $this->success($lista);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Codigo' => 'string|max:100',
            'Nombre' => 'required|string|max:100',
            'Tratamiento' => 'string',
            'status' => 'boolean',
        ]);

        $causal = CausalNoConforme::create($request->all());

        return $this->success($causal);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        $request->validate([
            'Codigo' => 'string|max:100',
            'Nombre' => 'string|max:100',
            'Tratamiento' => 'string',
            'status' => 'boolean',
        ]);

        $causalNoConforme = CausalNoConforme::find($id);
        $causalNoConforme->update($request->all());

        return $this->success($causalNoConforme);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
