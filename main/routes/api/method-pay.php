<?php

use App\Http\Controllers\MethodPayController;
use Illuminate\Support\Facades\Route;

Route::apiResource("method-pays", MethodPayController::class);
