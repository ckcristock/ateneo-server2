<?php

use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get("paginateUnits", [UnitController::class, 'paginate']);
Route::apiResource('units', UnitController::class);
