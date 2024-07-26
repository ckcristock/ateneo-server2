<?php

use App\Http\Controllers\ThirdPartyRegimeController;
use Illuminate\Support\Facades\Route;

Route::get('paginateThirdPartyRegime', [ThirdPartyRegimeController::class, 'paginate']);
Route::apiResource('third-party-regime', ThirdPartyRegimeController::class);
