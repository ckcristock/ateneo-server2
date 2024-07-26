<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Traits\ApiResponser;

class BankController extends Controller
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
            Bank::orderBy('name', 'DESC')
                ->get(['name As name', 'id As value'])
        );
    }
}
