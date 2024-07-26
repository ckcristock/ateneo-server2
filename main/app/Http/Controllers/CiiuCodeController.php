<?php

namespace App\Http\Controllers;

use App\Models\CiiuCode;
use App\Traits\ApiResponser;

class CiiuCodeController extends Controller
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
            CiiuCode::all()
        );
    }
}
