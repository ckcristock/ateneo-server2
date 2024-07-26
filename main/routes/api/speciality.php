<?php

use App\Http\Controllers\SpecialityController;
use Illuminate\Support\Facades\Route;

Route::apiResource("specialities", SpecialityController::class);
Route::get("get-specialties/{sede?}/{procedure?}", [SpecialityController::class, "index",]);
Route::post("get-specialties-type-service", [SpecialityController::class, "getForTypeService",]);
Route::get("get-specialties-by-procedure/{cup?}", [SpecialityController::class, "byProcedure"]);
Route::get("paginate-especialities", [SpecialityController::class, "paginate"]);
