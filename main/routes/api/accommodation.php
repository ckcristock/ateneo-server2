<?php

use App\Http\Controllers\AccommodationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('accommodations', AccommodationController::class);

Route::controller(AccommodationController::class)->group(function () {
    Route::get('paginate-accommodations', 'paginate');
    Route::post('restore-accommodation', 'restore');
});

