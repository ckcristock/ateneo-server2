<?php

use App\Http\Controllers\ProvisionController;
use Illuminate\Support\Facades\Route;

Route::apiResource("provisions", ProvisionController::class);