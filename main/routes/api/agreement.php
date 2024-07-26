<?php

use App\Http\Controllers\AgreementController;
use Illuminate\Support\Facades\Route;

Route::apiResource("agreements", AgreementController::class);
