<?php

use App\Http\Controllers\PayrollManagerController;
use Illuminate\Support\Facades\Route;

Route::apiResource('payroll-manager', PayrollManagerController::class);
