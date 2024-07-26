<?php

use App\Http\Controllers\DurationController;
use Illuminate\Support\Facades\Route;

Route::get("get-durations", [DurationController::class, "index"]);
