<?php

use App\Http\Controllers\LevelController;
use Illuminate\Support\Facades\Route;

Route::apiResource("levels", LevelController::class);
