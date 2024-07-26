<?php

use App\Http\Controllers\PersonInvolvedController;
use Illuminate\Support\Facades\Route;

Route::apiResource('annotation', PersonInvolvedController::class);
