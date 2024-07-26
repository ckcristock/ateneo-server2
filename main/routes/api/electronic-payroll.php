<?php

use App\Http\Controllers\ElectronicPayrollController;
use Illuminate\Support\Facades\Route;

Route::get('electronic-payroll/{id}', [ElectronicPayrollController::class, 'getElectronicPayroll']);
Route::get('electronic-payroll-paginate/{id}', [ElectronicPayrollController::class, 'paginate']);
Route::get('electronic-payroll-statistics/{id}', [ElectronicPayrollController::class, 'statistics']);
Route::delete('electronic-payroll/{id}', [ElectronicPayrollController::class, 'deleteElectroincPayroll']);
