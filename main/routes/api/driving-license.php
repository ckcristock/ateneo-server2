<?php

use App\Http\Controllers\DrivingLicenseController;
use Illuminate\Support\Facades\Route;

Route::get('paginateDrivingLicences', [DrivingLicenseController::class, 'paginate']);
Route::apiResource('drivingLicenses', DrivingLicenseController::class);
