<?php

use App\Http\Controllers\PackagingController;
use Illuminate\Support\Facades\Route;

Route::apiResource('packaging', PackagingController::class);
Route::get('packaging-paginate', [PackagingController::class, 'paginate']);
