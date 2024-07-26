<?php

use App\Http\Controllers\PayVacationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('pay-vacation', PayVacationController::class);
