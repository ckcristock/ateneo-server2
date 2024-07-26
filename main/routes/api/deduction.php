<?php

use App\Http\Controllers\DeductionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('deductions', DeductionController::class);
