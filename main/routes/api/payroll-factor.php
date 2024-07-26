<?php

use App\Http\Controllers\PayrollFactorController;
use Illuminate\Support\Facades\Route;

Route::get('payroll-factor-people', [PayrollFactorController::class, 'indexByPeople']);
Route::apiResource('payroll-factor', PayrollFactorController::class);
Route::get('payroll-factor-people-count', [PayrollFactorController::class, 'count']);
Route::get('payroll-factor-download', [PayrollFactorController::class, 'payrollFactorDownload']);
