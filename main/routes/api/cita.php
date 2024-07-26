<?php

use App\Http\Controllers\CitaController;
use Illuminate\Support\Facades\Route;

Route::post('mycita', [CitaController::class, 'mycita']);

