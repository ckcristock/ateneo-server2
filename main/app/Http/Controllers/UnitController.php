<?php

namespace App\Http\Controllers;
use App\Models\Unit;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    use ApiResponser;
    
    public function index()
    {

        return $this->success(
            Unit::get(['name As text', 'id AS value'])
        );
    }

    public function paginate()
    {

        return $this->success(
            Unit::orderBy('name')
                ->when(request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->paginate(request()->get('pageSize', 50), ['*'], 'page', request()->get('page', 1))
        );
    }

    public function store(Request $request)
    {
        try {
            $unit = Unit::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($unit->wasRecentlyCreated)
                ?
                $this->success([
                    'title' => '¡Creado con éxito!',
                    'text' => 'La unidad ha sido creada satisfactoriamente'
                ])
                :
                $this->success([
                    'title' => '¡Actualizado con éxito!',
                    'text' => 'La unidad ha sido actualizada satisfactoriamente'
                ]);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        return Unit::find($id, ['name As text', 'id As value']);
    }
}
