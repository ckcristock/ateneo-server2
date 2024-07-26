<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Models\TypeLocation;
use Illuminate\Http\Request;

class TypeLocationController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(TypeLocation::orderBy('name', 'DESC')->get(['name As text', 'id As value']));

    }
}
