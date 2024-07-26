<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Traits\ApiResponser;

class LevelController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(Level::get(['name As text', 'id As value']));
    }
}
