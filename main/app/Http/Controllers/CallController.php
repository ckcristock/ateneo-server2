<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CallController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            return $this->success(Call::all());
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
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
            Call::create($request->all());
            return $this->success('Llamada guardada correctamente :)');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }
}
