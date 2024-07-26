<?php

use App\Http\Controllers\CompanyPaymentConfigurationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('companyPayment', CompanyPaymentConfigurationController::class);
