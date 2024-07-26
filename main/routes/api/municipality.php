<?php

use App\Http\Controllers\MunicipalityController;
use Illuminate\Support\Facades\Route;

Route::apiResource('municipalities', MunicipalityController::class);
Route::apiResource("cities", MunicipalityController::class);
Route::get('paginateMunicipality', [MunicipalityController::class, 'paginate']);
Route::get('all-municipalities', [MunicipalityController::class, 'allMunicipalities']);
Route::get('municipalities-for-dep/{id}', [MunicipalityController::class, 'municipalitiesForDep']);
