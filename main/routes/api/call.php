<?php

use App\Http\Controllers\CallController;
use Illuminate\Support\Facades\Route;

Route::apiResource("calls", CallController::class);
