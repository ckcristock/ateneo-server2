<?php

use App\Http\Controllers\ContractTermController;
use Illuminate\Support\Facades\Route;

Route::apiResource('contract-terms', ContractTermController::class);
Route::get('paginate-contract-term', [ContractTermController::class, 'paginate']);
