<?php

use App\Http\Controllers\RCuentaMedicaController;
use Illuminate\Support\Facades\Route;

Route::apiResource("r-cuentas", RCuentaMedicaController::class);
