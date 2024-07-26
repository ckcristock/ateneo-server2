<?php

use App\Http\Controllers\ColorController;
use Illuminate\Support\Facades\Route;

Route::apiResource('get-cups-colors', ColorController::class);
