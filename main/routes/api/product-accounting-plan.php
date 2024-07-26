<?php

use App\Http\Controllers\ProductAccountingPlanController;
use Illuminate\Support\Facades\Route;

Route::apiResource("product-accounting", ProductAccountingPlanController::class);
