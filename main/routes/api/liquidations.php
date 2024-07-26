<?php

use App\Http\Controllers\LiquidationsController;
use Illuminate\Support\Facades\Route;

Route::apiResource('liquidation', LiquidationsController::class);
Route::get('liquidation/download/{person_id}', [LiquidationsController::class, 'download']);
