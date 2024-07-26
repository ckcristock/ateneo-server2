<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpecialityResource;
use App\Models\Cup;
use App\Models\Speciality;
use App\Traits\ApiResponser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SpecialityController extends Controller
{

    use ApiResponser;

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index($sede = 0)
    {
        return SpecialityResource::collection(Speciality::sortedByName()->get(['id', 'name']));
    }

    public function getForTypeService(Request $request)
    {
        /* $cups = Cup::whereHas('type_service', function($q) use ($request) {
            $q->where('type_service_id', $request->type_service_id);
        })->pluck('id');
        return $this->success(
            Speciality::whereHas('cups', function($q) use ($cups) {
                $q->whereIn('cup_id', $cups);
            })->get(['id as value', 'name as text'])
        ); */

        return $this->success(
            Speciality::get(['id as value', 'name as text'])
        );
    }

    public function paginate()
    {
        try {
            return $this->success(
                Speciality::sortedByName()
                    ->when(request()->get('name'), function (Builder $q) {
                        $q->where('name', 'like', '%' . request()->get('name') . '%');
                    })
                    ->when(request()->get('code'), function (Builder $q) {
                        $q->where('id', 'like', '%' . request()->get('code') . '%');
                    })
                    ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
            );
        } catch (\Throwable $th) {
            return  $this->errorResponse([$th->getMessage(), $th->getFile(), $th->getLine()]);
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
            $Speciality = Speciality::updateOrCreate(['id' => request()->get('id')],  toUpper(request()->all()));
            return ($Speciality->wasRecentlyCreated === true) ? response()->success('creado con exito') : response()->success('Actualizado con exito');
        } catch (\Throwable $th) {
            return  $this->errorResponse([$th->getMessage(), $th->getFile(), $th->getLine()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Speciality  $speciality
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Speciality $speciality)
    {
        return response()->success($speciality);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Speciality  $speciality
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Speciality $speciality)
    {
        try {
            $speciality->status =  request()->get('status');
            $speciality->save();
            return  response()->success('Actualizado con exito');
        } catch (\Throwable $th) {
            return  $this->errorResponse([$th->getMessage(), $th->getFile(), $th->getLine()]);
        }
    }

    public function byProcedure($procedure = 0)
    {
        return Cup::find($procedure)->specialities()->get(['specialities.id as value', 'specialities.name as text']);
    }
}
