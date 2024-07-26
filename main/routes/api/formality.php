<?php

use App\Http\Controllers\FormalityController;
use Illuminate\Support\Facades\Route;

Route::apiResource("formality", FormalityController::class);
