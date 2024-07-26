<?php

use App\Http\Controllers\CompanyConfigurationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('company-configuration', CompanyConfigurationController::class);