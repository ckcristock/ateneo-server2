<?php

use App\Http\Controllers\TaxiCityController;
use Illuminate\Support\Facades\Route;

Route::apiResource('taxi-city', TaxiCityController::class);
