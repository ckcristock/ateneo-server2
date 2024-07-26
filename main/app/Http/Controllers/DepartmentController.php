<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Traits\ApiResponser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    use ApiResponser;
    public function index(Request $request)
    {
        $countryId = $request->get('country_id');
        return $this->success(
            Department::
            when($countryId, function ($q, $fill) {
                $q->where('country_id', $fill);
            })
            ->orderBy('name')->select(DB::raw('UPPER(name) AS text, id AS value'))->get()
        );
    }

    public function paginate()
    {
        return $this->success(
            Department::orderBy('name')
                ->when(request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function show($country_id)
    {
        return $this->success(Department::where('country_id', $country_id)
            ->orderBy('name', 'asc')
            ->when(request()->get('name'), function ($q, $fill) {
                $q->where('name', 'like', '%' . $fill . '%');
            })
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1)));
        //->get(['name as text', 'id as value']));
    }

    /**
     * Esta función sirve para crear y actualizar
     */
    public function store(Request $request)
    {
        try {
            $validator = false;
            $validatorCode = false;
            if ($request->id) {
                $dep = Department::find($request->id);
                if ($dep->name != $request->name || $dep->country_id != $request->country_id || $dep->dian_code != $request->dian_code || $dep->dane_code != $request->dane_code) {
                    $validator = Department::where('name', $request->name)->where('country_id', $request->country_id)->where('id', '!=', $request->id)->exists();
                    //$validatorCode = Department::where('dian_code', $request->dian_code)->orWhere('dane_code', $request->dane_code)->where('id', '!=', $request->id)->exists();
                }
            } else {
                $validator = Department::where('name', $request->name)->where('country_id', $request->country_id)->exists();
                //$validatorCode = Department::where('dian_code', $request->dian_code)->orWhere('dane_code', $request->dane_code)->exists();
            }
            if (!$validator && !$validatorCode) {
                $departamentos = Department::updateOrCreate(['id' => $request->get('id')], $request->all());
                return ($departamentos->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
            } else if ($validator) {
                throw new ModelNotFoundException('Ya existe un departamento con el mismo nombre que pertenece a este país');
            } else if ($validatorCode) {
                throw new ModelNotFoundException('Ya existe un departamento con el mismo código');
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }
}
