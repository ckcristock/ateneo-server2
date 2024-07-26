<?php

use App\Http\Controllers\SeveranceFundController;
use Illuminate\Support\Facades\Route;

Route::get('paginateSeveranceFunds', [SeveranceFundController::class, 'paginate']);
Route::apiResource('severance-funds', SeveranceFundController::class);
