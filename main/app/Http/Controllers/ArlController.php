<?php

namespace App\Http\Controllers;

use App\Models\Arl;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;


class ArlController extends Controller
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
                Arl::all(['id as value', 'name as text'])
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    public function paginate(Request $request)
    {
        return $this->success(
            Arl::orderBy('status')
                ->orderBy('name')
                ->when($request->name, function ($q, $fill) {
                    $q->where('name', 'like', "%$fill%");
                })
                ->when($request->code, function ($q, $fill) {
                    $q->where('code', 'like', "%$fill%");
                })
                ->when($request->nit, function ($q, $fill) {
                    $q->where('nit', 'like', "%$fill%");
                })
                ->when($request->status, function ($q, $fill) {
                    $q->where('status', $fill);
                })
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function store(Request $request)
    {
        try {
            $arl = Arl::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($arl->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                if (strpos($e->getMessage(), 'arl_nit_unique') !== false) {
                    return $this->error('El NIT ya existe.', 422);
                } elseif (strpos($e->getMessage(), 'arl_code_unique') !== false) {
                    return $this->error('El código ya existe.', 422);
                }
            }
            return $this->error('Error al guardar el registro.', 500);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
