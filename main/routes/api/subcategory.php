<?php

use App\Http\Controllers\SubcategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource("subcategory", SubcategoryController::class);
Route::get('getsubforcat/{id}', [SubcategoryController::class, 'getSubForCat']);
Route::put("subcategory-active/{id}", [SubcategoryController::class, 'turningOnOff']);
Route::delete("subcategory-variable/{id}", [SubcategoryController::class, 'deleteVariable']);
Route::get("list-subcategories", [SubcategoryController::class, 'listSubcategories']);
Route::get("subcategory-field/{id}", [SubcategoryController::class, 'getField']);
Route::get("subcategory-edit/{id?}/{idSubcategoria}", [SubcategoryController::class, 'getFieldEdit']);
Route::get('subcategory-paginate', [SubcategoryController::class, 'paginate']);
