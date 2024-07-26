<?php

namespace App\Http\Controllers;

use App\Models\Reason;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ReasonController extends Controller
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
            Reason::orderBy('observation', 'DESC')
                ->get(['observation As name', 'id As value'])
        );
    }
}
