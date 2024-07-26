<?php

use App\Http\Controllers\FiscalResponsibilityController;
use Illuminate\Support\Facades\Route;

Route::apiResource('fiscal-responsibility', FiscalResponsibilityController::class);
Route::get('paginateFiscalResponsibility', [FiscalResponsibilityController::class, 'paginate']);
