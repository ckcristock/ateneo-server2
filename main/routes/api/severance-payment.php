<?php

use App\Http\Controllers\SeverancePaymentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('severance-payments', SeverancePaymentController::class);
Route::get('severance-payment-paginate', [SeverancePaymentController::class, 'paginate']);
Route::get('get-severance-payment', [SeverancePaymentController::class, 'getSeverancePayment']);
Route::get('severance-payments-validate', [SeverancePaymentController::class, 'validatPay']);
