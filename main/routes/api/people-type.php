<?php

use App\Http\Controllers\PeopleTypeController;
use Illuminate\Support\Facades\Route;

Route::get("people-type-custom", [PeopleTypeController::class, "indexCustom"]);
Route::apiResource("people-type", PeopleTypeController::class);
