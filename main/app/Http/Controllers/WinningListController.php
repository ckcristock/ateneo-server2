<?php

namespace App\Http\Controllers;

use App\Models\WinningList;
use App\Traits\ApiResponser;

class WinningListController extends Controller
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
            WinningList::all()
        );
    }
}
