<?php

namespace App\Http\Controllers;

use App\Models\CenterCost;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CenterCostController extends Controller
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
            CenterCost::all(['name as text', 'id as values'])
        );
    }
}
