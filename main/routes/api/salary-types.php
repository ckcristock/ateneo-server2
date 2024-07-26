<?php

use App\Http\Controllers\SalaryTypesController;
use Illuminate\Support\Facades\Route;

Route::get('paginateSalaryType', [SalaryTypesController::class, 'paginate']);
Route::apiResource('salaryTypes', SalaryTypesController::class);
