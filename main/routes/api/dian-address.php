<?php

use App\Http\Controllers\DianAddressController;
use Illuminate\Support\Facades\Route;

Route::apiResource('dian-address', DianAddressController::class);
