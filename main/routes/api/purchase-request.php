<?php

use App\Http\Controllers\PurchaseRequestController;
use Illuminate\Support\Facades\Route;

Route::apiResource("purchase-request", PurchaseRequestController::class);
Route::get("paginate-purchase-request", [PurchaseRequestController::class, 'paginate']);
Route::get("paginate-purchase-request-orden-compra", [PurchaseRequestController::class, 'paginatePurchase']);
Route::post("get-products-purchase-request-orden-compra", [PurchaseRequestController::class, 'getProductsByPurchaseRequestIds']);
Route::get("get-product-typeahead", [PurchaseRequestController::class, 'getProducts']);
Route::get("quotation-purchase-request/{id}/{value}", [PurchaseRequestController::class, 'getQuotationPurchaserequest']);
Route::get("save-quotation-approved/{id}", [PurchaseRequestController::class, 'saveQuotationApproved']);
Route::post("save-quotation-purchase-request/", [PurchaseRequestController::class, 'saveQuotationPurchaseRequest']);
Route::get("datos-purchase-request", [PurchaseRequestController::class, 'getDatosPurchaseRequest']);
