<?php

namespace App\Http\Controllers;

use App\Models\PeopleType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PeopleTypeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(PeopleType::all());
    }

    public function indexCustom()
    {
        return $this->success(PeopleType::get(['name As text', 'id As value']));
    }
}
