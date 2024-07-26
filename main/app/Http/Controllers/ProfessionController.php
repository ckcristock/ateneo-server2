<?php

namespace App\Http\Controllers;

use App\Models\Profession;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ProfessionController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(
            Profession::all(['name as text', 'id as value'])
        );
    }

    public function paginate()
    {
        return $this->success(
            Profession::orderBy('name')
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
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
            return $this->success(
                Profession::updateOrCreate(['id' => $request->get('id')], $request->all())
            );
        } catch (\Throwable $th) {
            return $this->errorResponse([$th->getMessage(), $th->getFile(), $th->getLine()]);
        }
    }
}
