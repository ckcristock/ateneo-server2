<?php

use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::get('companyData/{id}', [CompanyController::class, 'getBasicData']);
Route::post('saveCompanyData', [CompanyController::class, 'saveCompanyData']);
Route::get("get-companys/{query?}", [CompanyController::class, "index"]);
Route::get("get-companys-based-on-city/{company?}", [CompanyController::class, "getCompanyBaseOnCity"]);
Route::get('/company-global', [CompanyController::class, 'getGlobal']);
Route::apiResource("company", CompanyController::class);
