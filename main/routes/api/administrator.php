<?php

use App\Http\Controllers\AdministratorController;
use Illuminate\Support\Facades\Route;

Route::apiResource("eps", AdministratorController::class);
Route::apiResource("administrators", AdministratorController::class);
Route::post("save-eps", [AdministratorController::class, 'saveEps']);
