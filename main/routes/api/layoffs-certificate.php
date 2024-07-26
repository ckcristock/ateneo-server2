<?php

use App\Http\Controllers\LayoffsCertificateController;
use Illuminate\Support\Facades\Route;

Route::get('download-layoffs-certificate/{id}', [LayoffsCertificateController::class, 'pdf']);
Route::apiResource('layoffs-certificate', LayoffsCertificateController::class);
Route::get('paginate-layoffs-certificate', [LayoffsCertificateController::class, 'paginate']);
