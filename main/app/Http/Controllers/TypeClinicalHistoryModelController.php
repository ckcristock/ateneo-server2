<?php

namespace App\Http\Controllers;

use App\Models\ClinicalHistoryModel;

class TypeClinicalHistoryModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forSelect()
    {
        try {
            $clinicalHistoryModel = ClinicalHistoryModel::find(request()->input('id'))->typeClinicalHistoryModel()->get(['name as text', 'id as value']);
            return response()->success($clinicalHistoryModel);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), 400);
        }
    }
}
