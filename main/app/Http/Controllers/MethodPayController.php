<?php

namespace App\Http\Controllers;

use App\Models\MethodPay;
use App\Traits\ApiResponser;

class MethodPayController extends Controller
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
            MethodPay::orderBy('name', 'DESC')
                ->get(['name As name', 'id As value'])
        );
    }
}
