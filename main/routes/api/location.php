<?php

use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get("get-sedes/{ips}/{procedure}", [LocationController::class, "index"]);
Route::get('paginate-locations', [LocationController::class, 'paginate']);
Route::get("get-sedes/{ips?}/{procedure?}", [LocationController::class, "index"]);
Route::get("sedesPaginate", [LocationController::class, "paginate"]);
