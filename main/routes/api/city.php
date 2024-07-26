<?php

use App\Http\Controllers\CityController;
use Illuminate\Support\Facades\Route;

Route::get('cities-by-municipalities/{id}', [CityController::class, 'showByMunicipality']);
Route::get('paginateCities', [CityController::class, 'paginate']);
Route::apiResource('city', CityController::class);
