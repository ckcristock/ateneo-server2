<?php

use App\Http\Controllers\DispensingPointController;
use Illuminate\Support\Facades\Route;

Route::apiResource("dispensing", DispensingPointController::class);
Route::post('dispensing/{personId}', [DispensingPointController::class, 'setPerson']);
