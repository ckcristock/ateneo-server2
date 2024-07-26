<?php

namespace App\Http\Controllers;

use App\Models\ClinicalHistoryModel;
use Illuminate\Http\Request;

class ClinicalHistoryModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $clinicalHistoryModel = ClinicalHistoryModel::query();
            $clinicalHistoryModel->when(request()->input('search') != '', function ($q) {
                $q->where(function ($query) {
                    $query->where('description', 'like', '%' . request()->input('search') . '%')
                        ->orWhere('name', 'like', '%' . request()->input('search') . '%');
                });
            });

            return response()->success($clinicalHistoryModel
                ->with('typeClinicalHistoryModel', 'subTypeClinicalHistoryModel')
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1)));
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $req = request()->get('modulesForm');
        $data = collect([]);

        foreach ($req[0]['variables'] as $datum) {

            $data->put($datum['name'], $datum['valor']);

        }

        ClinicalHistoryModel::create($data->all());
        return response()->success('Operacion Realizada correctamente');

    }
}
