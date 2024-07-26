<?php

use App\Http\Controllers\PensionFundController;
use Illuminate\Support\Facades\Route;

Route::get('paginatePensionFund', [PensionFundController::class, 'paginate']);
Route::apiResource('pension-funds', PensionFundController::class);
