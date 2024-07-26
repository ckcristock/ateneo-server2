<?php

use App\Http\Controllers\FixedTurnHourController;
use Illuminate\Support\Facades\Route;

Route::get('fixed-turn-hours', [FixedTurnHourController::class, 'index']);
