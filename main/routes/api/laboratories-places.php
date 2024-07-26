<?php

use App\Http\Controllers\LaboratoriesPlacesController;
use Illuminate\Support\Facades\Route;

Route::apiResource('laboratories-places', LaboratoriesPlacesController::class);
