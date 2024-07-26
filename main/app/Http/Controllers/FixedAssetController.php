<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    use ApiResponser;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            return $this->success(
                FixedAsset::create($request->all())
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
        }
    }
}
