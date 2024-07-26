<?php

use App\Http\Controllers\CountableIncomeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('countable_incomes', CountableIncomeController::class);
