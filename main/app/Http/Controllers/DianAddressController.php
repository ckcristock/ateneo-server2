<?php

namespace App\Http\Controllers;

use App\Models\DianAddress;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class DianAddressController extends Controller
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
            DianAddress::all()
        );
    }
}
