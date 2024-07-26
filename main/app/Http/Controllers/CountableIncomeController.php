<?php

namespace App\Http\Controllers;

use App\Models\CountableIncome;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CountableIncomeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->success(
            CountableIncome::when($request->get('type'), function ($q, $fill) {
                $q->where('type', $fill);
            })
                ->where('state', 1)
                ->get(['id', 'concept', 'type', 'state', 'editable', 'id as value', 'concept as text'])
        );
    }
}
