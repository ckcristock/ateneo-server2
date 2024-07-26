<?php

use App\Http\Controllers\DependencyController;
use Illuminate\Support\Facades\Route;

Route::get('filter-all-depencencies', [DependencyController::class, 'dependencies']);
Route::apiResource('dependencies', DependencyController::class);
