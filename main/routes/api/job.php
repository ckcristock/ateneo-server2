<?php

use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;

Route::apiResource('jobs', JobController::class);
Route::get('jobs-preview', [JobController::class, 'getPreview']);
Route::post('jobs/set-state/{id}', [JobController::class, 'setState']);
