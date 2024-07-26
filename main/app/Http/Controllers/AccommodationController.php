<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Accommodation;
use App\Models\Person;

class AccommodationController extends Controller
{
    /**
     * Se está manejando borrado lógico y restauración quitando
     * la fecha del deleted_at
     * Para borrado físico se debe hacer por BD
     */

    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(Accommodation::all());
    }

    public function paginate()
    {
        return $this->success(
            Accommodation:: //with('hotels')
                withTrashed()
                ->when(request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', "%$fill%");
                })
                ->where('company_id', $this->getCompany())
                ->orderBy('deleted_at')
                ->paginate(request()->get('pageSize', 5), ['*'], 'page', request()->get('page', 1))
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
            $nuevo = Accommodation::withTrashed()->updateOrCreate(['id' => $request->id], [
                'name' => $request->name,
                'company_id' => $this->getCompany()
            ]);
            return ($nuevo->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 204);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->success(Accommodation::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        return $this->success('Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $register = Accommodation::find($id);
            $register->delete();
            if ($register) {
                return $this->success('Registro eliminado exitosamente');
            } else {
                return $this->error('Occurrió un error al intentar borrar', 204);
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 204);
        }
    }

    /**
     * Handle the User "restored" event.
     *
     * @param   $instance
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Request $request)
    {
        try {
            $data = $request->all()['data'];

            $register = Accommodation::withTrashed()
                ->where('id', $data['id'])
                ->restore();

            if ($register) {
                return $this->success('Registro restaurado exitosamente');
            } else {
                return $this->error('Occurrió un error al intentar activar', 204);
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 204);
        }
    }
}
