<?php

use App\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::apiResource('group', GroupController::class);
Route::get('group-company', [GroupController::class, 'getGroupCompany']);
