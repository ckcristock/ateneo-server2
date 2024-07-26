<?php

use App\Http\Controllers\TypeLocationController;
use Illuminate\Support\Facades\Route;

Route::apiResource("type-locations", TypeLocationController::class);
