<?php

use App\Http\Controllers\CatalogoController;
use Illuminate\Support\Facades\Route;

Route::apiResource("catalogo", CatalogoController::class);
