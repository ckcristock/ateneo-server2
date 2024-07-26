<?php

use App\Http\Controllers\FeeController;
use Illuminate\Support\Facades\Route;

Route::apiResource("fees", FeeController::class);
