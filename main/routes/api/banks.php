<?php

use App\Http\Controllers\BanksController;
use Illuminate\Support\Facades\Route;

Route::get('paginateBanks', [BanksController::class, 'paginate']);
Route::apiResource("banks", BanksController::class);
