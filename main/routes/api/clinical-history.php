<?php

use App\Http\Controllers\ClinicalHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('get-clinical-historial', [ClinicalHistoryController::class, 'index']);
Route::get('get-clinical-historial-detail', [ClinicalHistoryController::class, 'show']);
