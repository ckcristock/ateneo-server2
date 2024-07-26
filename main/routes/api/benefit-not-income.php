<?php

use App\Http\Controllers\BenefitNotIncomeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('countable-not-incomes', BenefitNotIncomeController::class);
