<?php

namespace App\Http\Controllers;

use App\Models\SalaryTypes;
use App\Models\TypeSalary;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class SalaryTypesController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(SalaryTypes::get(['name As text', 'id As value']));
    }

    public function paginate()
    {
        $data = Request()->all();
        $page = key_exists('page', $data) ? $data['page'] : 1;
        $pageSize = key_exists('pageSize', $data) ? $data['pageSize'] : 10;
        return $this->success(
            SalaryTypes::when(
                Request()->get('name'),
                function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                }
            )
                ->paginate($pageSize, ['*'], 'page', $page)
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
            $typeSalary = SalaryTypes::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($typeSalary->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalaryTypes  $typeSalary
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SalaryTypes $salaryTypes)
    {
        try {
            $typeSalary = SalaryTypes::find(request()->get('id'));
            $typeSalary->update(request()->all());
            return $this->success('Tipo de salario actualizado correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalaryTypes  $typeSalary
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $typeSalary = SalaryTypes::findOrFail($id);
            $typeSalary->delete();
            return $this->success('Tipo de salario eliminado correctamente', 204);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
