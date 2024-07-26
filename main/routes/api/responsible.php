<?php

use App\Http\Controllers\ResponsibleController;
use Illuminate\Support\Facades\Route;

Route::apiResource('responsibles', ResponsibleController::class);
