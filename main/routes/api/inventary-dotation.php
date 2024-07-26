<?php

use App\Http\Controllers\InventaryDotationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('inventary-dotation', InventaryDotationController::class);
Route::get('inventary-dotation-by-category', [InventaryDotationController::class, 'indexGruopByCategory']);
Route::get('inventary-dotation-statistics', [InventaryDotationController::class, 'statistics']);
Route::get('inventary-dotation-stock', [InventaryDotationController::class, 'getInventary']);
Route::get('get-selected', [InventaryDotationController::class, 'getSelected']);
Route::get('get-total-inventary', [InventaryDotationController::class, 'getTotatInventary']);
Route::get('inventary-dotation-stock-epp', [InventaryDotationController::class, 'getInventaryEpp']);
Route::get('inventary-dotation-download', [InventaryDotationController::class, 'download']);
Route::get('downloadeliveries/download/{inicio?}/{fin?}', [InventaryDotationController::class, 'downloadeliveries']);
