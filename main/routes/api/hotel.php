<?php

use App\Http\Controllers\HotelController;
use Illuminate\Support\Facades\Route;

Route::get('paginateHotels', [HotelController::class, 'paginate']);
Route::apiResource('hotels', HotelController::class);
