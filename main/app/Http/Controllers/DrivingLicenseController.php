<?php

namespace App\Http\Controllers;

use App\Models\DrivingLicense;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class DrivingLicenseController extends Controller
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
            DrivingLicense::where('state', '=', 'activo')
                ->get(['id as value', 'type as text'])
        );
    }

    public function paginate()
    {
        return $this->success(
            DrivingLicense::orderBy('state')
                    ->orderBy('type')
                    ->when(request()->get('tipo'), function ($q, $fill) {
                        $q->where('type', 'like', '%' . $fill . '%');
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
            $license = DrivingLicense::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($license->wasRecentlyCreated)
                ?
                $this->success([
                    'title' => '¡Creado con éxito!',
                    'text' => 'La licencia de conducción ha sido creada satisfactoriamente'
                ])
                :
                $this->success([
                    'title' => '¡Actualizado con éxito!',
                    'text' => 'La cicencia de conducción ha sido actualizada satisfactoriamente'
                ]);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
