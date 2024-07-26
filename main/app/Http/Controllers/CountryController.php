<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Traits\ApiResponser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CountryController extends Controller
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
            return $this->success(
                Country::where('state', '=', 'Activo')
                    ->orderBy('name', 'asc')
                    ->get(['name as text', 'id as value'])
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    public function allCountries()
    {
        return $this->success(
            Country::where('state', 'Activo')
                ->with(['departments' => function ($q) {
                    $q->select('*', 'name as text', 'id as value');
                }])
                ->orderByRaw("CASE WHEN name = 'Colombia' THEN 0 ELSE 1 END, name ASC")
                ->get(['*', 'name as text', 'id as value'])
        );
    }

    public function paginate()
    {
        return $this->success(
            Country::orderByRaw("CASE WHEN name = 'Colombia' THEN 0 ELSE 1 END, name ASC")
                ->when(request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
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

            $validator = false;
            $validatorCode = false;
            if ($request->id) {
                $mun = Country::find($request->id);
                if ($mun->name != $request->name || $mun->dian_code != $request->dian_code) {
                    $validator = Country::where('name', $request->name)->where('id', '!=', $request->id)->exists();
                    //$validatorCode = Country::where('dian_code', $request->dian_code)->where('id', '!=', $request->id)->exists();
                }
            } else {
                $validator = Country::where('name', $request->name)->exists();
                //$validatorCode = Country::where('dian_code', $request->dian_code)->exists();
            }
            if (!$validator && !$validatorCode) {
                $countries = Country::updateOrCreate(['id' => $request->get('id')], $request->all());
                return ($countries->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
            } else if ($validator) {
                return $this->error('Ya existe un país con el mismo nombre', 200);
            } else if ($validatorCode) {
                return $this->error('Ya existe un país con el mismo Código', 200);
            } else {
                return $this->error('Ocurrio un error', 200);
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }
}
