<?php

use App\Http\Controllers\BenefitIncomeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('countable-incomes', BenefitIncomeController::class);
