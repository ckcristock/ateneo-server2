<?php

use App\Http\Controllers\MemorandumTypesController;
use Illuminate\Support\Facades\Route;

Route::apiResource('type_memorandum', MemorandumTypesController::class);
Route::get('ListLimitated', [MemorandumTypesController::class, 'getListLimitated']);
