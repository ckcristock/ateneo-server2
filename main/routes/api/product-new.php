<?php

use App\Http\Controllers\ProductNewController;
use Illuminate\Support\Facades\Route;

Route::get("product2-paginate", [ProductNewController::class, 'paginate']);
Route::apiResource("product2", ProductNewController::class);
Route::get("product-create-data", [ProductNewController::class, 'getDataCreate']);
Route::get("variables-category/{id}", [ProductNewController::class, 'getVariablesCat']);
Route::get("variables-subcategory/{id}", [ProductNewController::class, 'getVariablesSubCat']);
Route::get('php/comprasnacionales/lista_productos', [ProductNewController::class, 'listarProductos']);
Route::get('php/comprasnacionales/lista_productos', [ProductNewController::class, 'listarProductos']);
