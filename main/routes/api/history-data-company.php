<?php

use App\Http\Controllers\HistoryDataCompanyController;
use Illuminate\Support\Facades\Route;

Route::apiResource('history-data-company', HistoryDataCompanyController::class);
