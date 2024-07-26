<?php

use App\Http\Controllers\TaxiControlller;
use Illuminate\Support\Facades\Route;

Route::get('paginateTaxis', [TaxiControlller::class, 'paginate']);
Route::apiResource('taxis', TaxiControlller::class);
