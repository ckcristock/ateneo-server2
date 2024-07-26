<?php

use App\Http\Controllers\RotatingTurnHourController;
use Illuminate\Support\Facades\Route;

Route::get('horarios/datos/generales/{semana}', [RotatingTurnHourController::class, 'getDatosGenerales']);
Route::apiResource('rotating-hour', RotatingTurnHourController::class);
