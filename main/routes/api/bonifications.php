<?php

use App\Http\Controllers\BonificationsController;
use Illuminate\Support\Facades\Route;

Route::get('countable_income', [BonificationsController::class, 'countable_income']);
Route::get('countable_income', [BonificationsController::class, 'countable_income']);
Route::apiResource('bonifications', BonificationsController::class);
