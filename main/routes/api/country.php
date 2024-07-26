<?php

use App\Http\Controllers\CountryController;
use Illuminate\Support\Facades\Route;

Route::get('paginateCountries', [CountryController::class, 'paginate']);
Route::apiResource('countries', CountryController::class);
Route::get('countries-with-departments', [CountryController::class, 'allCountries']);
