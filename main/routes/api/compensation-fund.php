<?php

use App\Http\Controllers\CompensationFundController;
use Illuminate\Support\Facades\Route;

Route::apiResource('compensation-funds', CompensationFundController::class);
Route::get('paginate-compensation-funds', [CompensationFundController::class, 'paginate']);
