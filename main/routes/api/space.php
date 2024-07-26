<?php

use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::post("space-cancel", [SpaceController::class, "cancel"]);
Route::get("spaces-statistics", [SpaceController::class, "statistics"]);
Route::get("spaces-statistics-detail", [SpaceController::class, "statisticsDetail"]);
Route::get("opened-spaces", [SpaceController::class, "index"]);
Route::get("opened-spaces/{especialidad?}/{profesional?}", [SpaceController::class, "indexCustom"]);
