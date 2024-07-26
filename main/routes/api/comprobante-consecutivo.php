<?php

use App\Http\Controllers\ComprobanteConsecutivoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('comprobante-consecutivo', ComprobanteConsecutivoController::class);
Route::get('paginate-comprobante-consecutivo', [ComprobanteConsecutivoController::class, 'paginate']);
Route::get('get-consecutivo/{table}', [ComprobanteConsecutivoController::class, 'getConsecutive']);
