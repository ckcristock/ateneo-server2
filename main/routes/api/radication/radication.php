<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RCuentaMedicaController;
use Illuminate\Support\Facades\Route;

//Dan Radication Routes
Route::get('get-company', [CompanyController::class, 'getCompanyByIdentifier']);
Route::post('save-radicacion', [RCuentaMedicaController::class, 'store']);
Route::get('get-companies', [CompanyController::class, 'getCompanies']);
Route::get('get-all-companies', [CompanyController::class, 'getAllCompanies']);
//End Radicatioon routes
