<?php

use App\Http\Controllers\FixedTurnController;
use Illuminate\Support\Facades\Route;

Route::apiResource('fixed-turns', FixedTurnController::class);
Route::get('fixed-turns_active', [FixedTurnController::class, 'activeFixedTurns']);
Route::post('fixed-turns/change-state/{id}', [FixedTurnController::class, 'changeState']);
Route::get('paginate-fixed-turns', [FixedTurnController::class, 'paginate']);
