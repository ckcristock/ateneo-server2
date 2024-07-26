<?php

use App\Http\Controllers\RotatingTurnController;
use Illuminate\Support\Facades\Route;

Route::post('rotating-turns/change-state/{id}', [RotatingTurnController::class, 'changeState']);
Route::get('get-rotating-turns', [RotatingTurnController::class, "paginate"]);
Route::apiResource('rotating-turns', RotatingTurnController::class);
