<?php

use App\Http\Controllers\ApplicationCertificateController;
use Illuminate\Support\Facades\Route;

Route::apiResource('aplication-certificate', ApplicationCertificateController::class);
Route::post('/aplication-certificate/{id}', [ApplicationCertificateController::class, 'update']);
