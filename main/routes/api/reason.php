<?php

use App\Http\Controllers\ReasonController;
use Illuminate\Support\Facades\Route;

Route::apiResource("reasons", ReasonController::class);
