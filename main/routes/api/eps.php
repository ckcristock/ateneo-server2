<?php

use App\Http\Controllers\EpsController;
use Illuminate\Support\Facades\Route;

Route::get("paginate-eps", [EpsController::class, "paginate"]);
Route::apiResource("epss", EpsController::class);
