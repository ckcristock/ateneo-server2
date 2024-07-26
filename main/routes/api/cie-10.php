<?php

use App\Http\Controllers\Cie10Controller;
use Illuminate\Support\Facades\Route;

Route::apiResource("cie10s", Cie10Controller::class);
Route::get("getall-cie10s", [Cie10Controller::class, "getAll"]);
