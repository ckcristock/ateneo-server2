<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->success(
            Position::when($request->get('dependency_id'), function ($q, $p) {
                $q->where('dependency_id', $p);
            })->get(['id as value', 'name as text', 'responsibilities'])
        );
    }

    public function positions()
    {
        return $this->success(
            Position::all(['id as value', 'name as text'])
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
            $position = Position::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($position->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
