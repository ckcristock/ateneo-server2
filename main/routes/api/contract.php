<?php

use App\Http\Controllers\ContractController;
use Illuminate\Support\Facades\Route;

Route::apiResource("contract", ContractController::class);
Route::apiResource("contract-for-select", ContractController::class);
Route::post("contracts", [ContractController::class, 'store']);
Route::get("get-payment-methods-contracts", [ContractController::class, 'getPaymentMethodsContracts']);
Route::get("get-attention-routes", [ContractController::class, 'getAttentionRoutes']);
Route::post("get-attention-routes-custom", [ContractController::class, 'getAttentionRoutesCustom']);
Route::get("contracts", [ContractController::class, 'paginate']);
Route::get("contracts-for-select", [ContractController::class, 'index']);
Route::get("contracts/{id?}", [ContractController::class, 'edit']);
