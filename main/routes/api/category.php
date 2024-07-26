<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource("category", CategoryController::class);
Route::get('list-categories', [CategoryController::class, 'listCategories']);
Route::get("category-field/{id}", [CategoryController::class, 'getField']);
Route::put("category-active/{id}", [CategoryController::class, 'turningOnOff']);
Route::delete("category-variable/{id}", [CategoryController::class, 'deleteVariable']);
Route::get("get-category-for-select", [CategoryController::class, 'indexForSelect']);
Route::get('category-paginate', [CategoryController::class, 'paginate']);
