<?php

namespace App\Http\Controllers;

use App\Models\MemorandumTypes;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class MemorandumTypesController extends Controller
{
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
        $data = Request()->all();
        $page = key_exists('page', $data) ? $data['page'] : 1;
        $pageSize = key_exists('pageSize', $data) ? $data['pageSize'] : 5;
        return $this->success(
            MemorandumTypes::select(
                'id as value',
                'name as text',
                'status'
            )
                ->where('company_id', '=', $this->getCompany())
                ->paginate($pageSize, ['*'], 'page', $page)
        );
    }

    public function getListLimitated()
    {
        return $this->success(
            MemorandumTypes::select(
                'id as value',
                'name as text',
                'status'
            )
                ->where('status', '=', 'Activo')
                ->where('company_id', '=', $this->getCompany())
                ->get()
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
            $data = $request->all();
            $data['company_id'] = $this->getCompany();
            MemorandumTypes::updateOrCreate(['id' => $request->get('id')], $data);
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }

    }
}
