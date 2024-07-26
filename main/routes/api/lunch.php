<?php

use App\Http\Controllers\LunchControlller;
use Illuminate\Support\Facades\Route;

Route::apiResource('lunch', LunchControlller::class);
Route::put('state-change', [LunchControlller::class, 'activateOrInactivate']);
