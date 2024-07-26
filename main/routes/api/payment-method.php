<?php

use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;

Route::apiResource('payment_methods', PaymentMethodController::class);
Route::get('paginatePaymentMethod', [PaymentMethodController::class, 'paginate']);
