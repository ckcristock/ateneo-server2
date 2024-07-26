<?php

namespace App\Http\Controllers;

use App\Models\SeveranceInterestPayment;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class SeveranceInterestPaymentController extends Controller
{
    use ApiResponser;

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SeveranceInterestPayment  $severanceInterestPayment
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        return $this->success(SeveranceInterestPayment::with('user', 'people')->find($id));
    }
}