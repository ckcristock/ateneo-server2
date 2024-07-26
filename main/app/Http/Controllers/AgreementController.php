<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Traits\ApiResponser;

class AgreementController extends Controller
{

    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            return $this->success(Agreement::get(['name As text', 'id As value']));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }
}
