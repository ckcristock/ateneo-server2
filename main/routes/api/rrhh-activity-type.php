<?php

use App\Http\Controllers\RrhhActivityTypeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('rrhh-activity-types', RrhhActivityTypeController::class);
Route::post('rrhh-activity-types/set', [RrhhActivityTypeController::class, 'setState']);
Route::get('rrhh-activity-types-all', [RrhhActivityTypeController::class, 'all']);
Route::get('rrhh-activity-types-actives', [RrhhActivityTypeController::class, 'actives']);
