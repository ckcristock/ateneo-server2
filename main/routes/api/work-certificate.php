<?php

use App\Http\Controllers\WorkCertificateController;
use Illuminate\Support\Facades\Route;

Route::get('download-work-certificate/{id}', [WorkCertificateController::class, 'pdf']);
Route::apiResource('work-certificate', WorkCertificateController::class);
Route::get('paginate-work-certificate', [WorkCertificateController::class, 'paginate']);
