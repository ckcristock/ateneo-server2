<?php

use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;

Route::get('filter-all-positions', [PositionController::class, 'positions']);
Route::apiResource('positions', PositionController::class);
