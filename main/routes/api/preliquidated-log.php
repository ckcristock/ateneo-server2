<?php

use App\Http\Controllers\PreliquidatedLogController;
use Illuminate\Support\Facades\Route;

Route::apiResource('preliquidation', PreliquidatedLogController::class);
