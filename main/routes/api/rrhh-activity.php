<?php

use App\Http\Controllers\RrhhActivityController;
use Illuminate\Support\Facades\Route;

Route::get('rrhh-activity-people/{id}', [RrhhActivityController::class, 'getPeople']);
Route::get('rrhh-activity/cancel/{id}', [RrhhActivityController::class, 'cancel']);
Route::post('rrhh-activity/cancelCycle/{code}', [RrhhActivityController::class, 'cancelCycle']);
Route::apiResource('rrhh-activity', RrhhActivityController::class);
