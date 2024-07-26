<?php

use App\Http\Controllers\ZonesController;
use Illuminate\Support\Facades\Route;

Route::get('all-zones', [ZonesController::class, 'allZones']);
Route::apiResource('zones', ZonesController::class);
