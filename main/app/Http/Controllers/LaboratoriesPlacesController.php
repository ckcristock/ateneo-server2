<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;

class LaboratoriesPlacesController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        return $this->success(DB::table('laboratories_places')
            ->select(
                'id As value',
                'name As text'
            )->get());
    }
}

