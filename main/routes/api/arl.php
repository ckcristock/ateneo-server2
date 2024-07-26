<?php

use App\Http\Controllers\ArlController;
use Illuminate\Support\Facades\Route;

Route::get('paginateArl', [ArlController::class, 'paginate']);
Route::apiResource('arl', ArlController::class);
