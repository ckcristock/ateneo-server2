<?php

use App\Http\Controllers\LateArrivalController;
use Illuminate\Support\Facades\Route;

Route::get('late_arrivals/statistics/{fechaInicio}/{fechaFin}', [LateArrivalController::class, 'statistics']);
Route::get('late_arrivals/data/{fechaInicio}/{fechaFin}', [LateArrivalController::class, 'getData'])->where([
    'fechaInicio' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
    'fechaFin' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
]);
Route::get('late_arrivals/paginate/{fechaInicio}/{fechaFin}', [LateArrivalController::class, 'getDataPaginated'])->where([
    'fechaInicio' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
    'fechaFin' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
]);
Route::get('late-arrivals/download/{inicio?}/{fin?}', [LateArrivalController::class, 'download']);
Route::apiResource('late-arrivals', LateArrivalController::class);
