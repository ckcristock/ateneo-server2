<?php

use App\Http\Controllers\CountableDeductionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('countable_deductions', CountableDeductionController::class);
