<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Traits\ApiResponser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MunicipalityController extends Controller
{
    use ApiResponser;

    public function allMunicipalities()
    {
        return $this->success(
            Municipality::orderByDesc('name')->all(['name as text', 'id as value'])
        );
    }
    public function municipalitiesForDep($idDep)
    {
        return $this->success(
            Municipality::where('department_id', $idDep)->orderBy('name', 'asc')->get(['name as text', 'id as value'])
        );
    }

    public function index()
    {
        $data = Municipality::with([
            'department' => function ($q) {
                $q->select('id', 'name');
            }
        ])
            ->with('department_')
            ->orderBy('municipalities.name', 'asc')
            ->when(Request()->get('department_id'), function ($q, $param) {
                $q->where('department_id', $param);
            })
            ->join('departments', 'municipalities.department_id', '=', 'departments.id')
            ->join('countries', 'departments.country_id', '=', 'countries.id')
            ->select(
                'municipalities.id',
                //'municipalities.percentage_product',
                //'municipalities.percentage_service',
                //'municipalities.name AS text',
                'municipalities.id AS value',
                'municipalities.department_id',
                'municipalities.code',
                'municipalities.abbreviation',
                DB::raw("UPPER(CONCAT(municipalities.name, ' - ', departments.name, ' - ', countries.name)) AS text")
            )
            ->get();
        /* ->selectRaw("CONCAT(municipalities.name, ' - ', department_.name, ' - ', countries.name) AS full_name")
            ->get(['id', 'percentage_product', 'percentage_service', 'name As text', 'id As value', 'department_id', 'code', 'abbreviation']); */
        return $this->success($data);
    }

    public function paginate()
    {
        return $this->success(
            Municipality::orderBy('name')
                ->with([
                    'department' => function ($q) {
                        $q->select('id', 'name')->when(Request()->get('department'), function ($q, $fill) {
                            $q->where('name', 'like', '%' . $fill . '%');
                        });
                    }
                ])
                ->whereHas('department', function ($q) {
                    $q->when(request()->get('department'), function ($q, $fill) {

                        $q->where('name', 'like', '%' . $fill . '%');
                    });
                })
                ->when(Request()->get('code'), function ($q, $fill) {
                    $q->where('code', 'like', '%' . $fill . '%');
                })
                ->when(Request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1))
        );
    }

    public function store(Request $request)
    {
        try {
            $validator = false;
            $validatorCode = false;
            if ($request->id) {
                $mun = Municipality::find($request->id);
                if ($mun->name != $request->name || $mun->department_id != $request->department_id || $mun->dian_code != $request->dian_code || $mun->dane_code != $request->dane_code) {
                    $validator = Municipality::where('name', $request->name)->where('department_id', $request->department_id)->where('id', '!=', $request->id)->exists();
                    //$validatorCode = Municipality::where('dian_code', $request->dian_code)->orWhere('dane_code', $request->dane_code)->where('id', '!=', $request->id)->exists();
                }
            } else {
                $validator = Municipality::where('name', $request->name)->where('department_id', $request->department_id)->exists();
                //$validatorCode = Municipality::where('dian_code', $request->dian_code)->orWhere('dane_code', $request->dane_code)->exists();
            }
            if (!$validator && !$validatorCode) {
                $municipality = Municipality::updateOrCreate(['id' => $request->get('id')], $request->all());
                return ($municipality->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
            } else if ($validator) {
                throw new ModelNotFoundException('Ya existe un municipio con el mismo nombre que pertenece a este departamento');
            } else if ($validatorCode) {
                throw new ModelNotFoundException('Ya existe un municipio con el mismo código');
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }

    /**
     * 25/10/22
     * Busca los munucipios de acuerdo el id de Estado
     */
    public function show($state_id)
    {
        $query = Municipality::where('department_id', $state_id)
            ->orderBy('name', 'asc')
            ->when(
                request()->get('name'),
                function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                }
            );

        if (request()->has('pageSize') || request()->has('page')) {
            $pageSize = request()->get('pageSize', 10);
            $page = request()->get('page', 1);
            $result = $query->paginate($pageSize, ['*'], 'page', $page);
        } else {
            $result = $query->get();
        }

        return $this->success($result);
    }

}
