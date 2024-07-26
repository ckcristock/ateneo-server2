<?php

use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::get('type_reportes', [ReporteController::class, 'getReportes']);
Route::get('info-grafical-by-regional', [ReporteController::class, 'getDataByRegional']);
Route::get('info-grafical-by-formality', [ReporteController::class, 'getDataByFormality']);
Route::get('info-grafical-by-deparment', [ReporteController::class, 'getDataByDepartment']);
Route::get('info-grafical-resume', [ReporteController::class, 'getDataByRegional', 'getDataByFormality', 'getDataByDepartment']);
Route::get('reporte', [ReporteController::class, 'general']);
