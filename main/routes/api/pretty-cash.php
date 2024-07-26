<?php

use App\Http\Controllers\PrettyCashController;
use Illuminate\Support\Facades\Route;


Route::apiResource('pretty-cash', PrettyCashController::class);
Route::get('pretty-cash-paginate', [PrettyCashController::class, 'paginate']);