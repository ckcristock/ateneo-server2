<?php

namespace App\Http\Controllers;

use App\Models\Duration;
use App\Traits\ApiResponser;

class DurationController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(Duration::query()->select('value', 'text')->orderBy('value', 'Asc')->get());
    }
}
