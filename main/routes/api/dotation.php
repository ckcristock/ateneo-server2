<?php

use App\Http\Controllers\DotationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('dotations', DotationController::class);
Route::post('dotations-update/{id}', [DotationController::class, 'update']);
Route::post('dotations-update-stock', [DotationController::class, 'updateStock']);
Route::post('dotations-approve/{id}', [DotationController::class, 'approve']);
Route::get('dotations-download/{id}', [DotationController::class, 'download']);
Route::get('dotations-total-types', [DotationController::class, 'getTotatlByTypes']);
Route::get('dotations-list-product', [DotationController::class, 'getListProductsDotation']);
Route::get('dotations-type', [DotationController::class, 'getDotationType']);
