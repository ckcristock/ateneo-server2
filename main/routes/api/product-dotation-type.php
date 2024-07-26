<?php

use App\Http\Controllers\ProductDotationTypeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('product-dotation-types', ProductDotationTypeController::class);
