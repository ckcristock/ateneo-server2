<?php

use App\Http\Controllers\RiskTypesController;
use Illuminate\Support\Facades\Route;

Route::get('paginateRiskTypes', [RiskTypesController::class, 'paginate']);
Route::apiResource('risk', RiskTypesController::class);
