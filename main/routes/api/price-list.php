<?php

use App\Http\Controllers\PriceListController;
use Illuminate\Support\Facades\Route;

Route::apiResource('price_lists', PriceListController::class);
Route::get('paginatePriceList', [PriceListController::class, 'paginate']);
