<?php

use App\Http\Controllers\SeveranceInterestPaymentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('severance-interest-payments', SeveranceInterestPaymentController::class);
