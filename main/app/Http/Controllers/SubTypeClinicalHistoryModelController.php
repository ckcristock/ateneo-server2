<?php

namespace App\Http\Controllers;

use App\Models\ClinicalHistoryModel;

class SubTypeClinicalHistoryModelController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function forSelect()
    {
        try {
            $clinicalHistoryModel = ClinicalHistoryModel::find(request()->input('id'))->subTypeClinicalHistoryModel()->get(['name as text', 'id as value']);
            return response()->success($clinicalHistoryModel);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), 400);
        }
    }
}
