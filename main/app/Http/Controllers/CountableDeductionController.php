<?php

namespace App\Http\Controllers;

use App\Models\CountableDeduction;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CountableDeductionController extends Controller
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
            CountableDeduction::where('state', 1)
                ->get(['id', 'concept', 'state', 'editable', 'id as value', 'concept as text'])
        );
    }
}
