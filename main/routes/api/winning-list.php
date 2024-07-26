<?php

use App\Http\Controllers\WinningListController;
use Illuminate\Support\Facades\Route;

Route::apiResource('winnings-list', WinningListController::class);
