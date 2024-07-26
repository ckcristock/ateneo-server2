<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource("product", ProductController::class);
Route::get('product-acta', [ProductController::class, 'getProductActa']);
Route::get('get-materials', [ProductController::class, 'getMaterials']);
Route::get("get-vars-producto", [ProductController::class, 'getVars']);
Route::get("get-actividad-producto", [ProductController::class, 'getActividad']);
Route::post("cambiar-estado-producto", [ProductController::class, 'cambiarEstado']);
