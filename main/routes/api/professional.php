<?php

use App\Http\Controllers\ProfessionalController;
use Illuminate\Support\Facades\Route;

Route::apiResource("professionals", ProfessionalController::class);
