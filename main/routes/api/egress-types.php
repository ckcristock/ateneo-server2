<?php

use App\Http\Controllers\EgressTypesController;
use Illuminate\Support\Facades\Route;

Route::get('paginateEgressTypes', [EgressTypesController::class, 'paginate']);
Route::apiResource('egress_types', EgressTypesController::class);

