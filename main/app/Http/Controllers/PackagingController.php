<?php

namespace App\Http\Controllers;

use App\Models\Packaging;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PackagingController extends Controller
{

    use ApiResponser;

    public function paginate()
    {
        return $this->success(
            Packaging::paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1))
        );
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(
            Packaging::get(['id as value', 'name as text'])
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
        $createOrUpdate = Packaging::updateOrCreate(['id' => $request->id], $request->all());
        return $this->success($createOrUpdate->wasRecentlyCreated ? 'Creado con éxito' : 'Actualizado con éxito');
    }
}
