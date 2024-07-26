<?php

use App\Http\Controllers\CupController;
use Illuminate\Support\Facades\Route;

Route::post("imports", [CupController::class, "import"]);
Route::apiResource("cups", CupController::class);
Route::get("paginate-cup", [CupController::class, "paginate"]);
Route::get("cup-types", [CupController::class, "getTypes"]);
