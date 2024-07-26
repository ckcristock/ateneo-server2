<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Traits\ApiResponser;

class ColorController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(Color::select('id as value', 'color as text')->get());
    }
}
