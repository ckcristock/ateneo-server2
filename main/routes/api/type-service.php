<?php

use App\Http\Controllers\TypeServiceController;
use Illuminate\Support\Facades\Route;

Route::get("type-service/formality/{id}", [TypeServiceController::class, 'allByFormality']);
Route::apiResource('type-service', TypeServiceController::class);
