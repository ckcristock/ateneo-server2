<?php

use App\Http\Controllers\HistoryRotatingTurnHourController;
use Illuminate\Support\Facades\Route;

Route::apiResource('history-rotating-hour', HistoryRotatingTurnHourController::class);
