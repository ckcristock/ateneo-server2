<?php

use App\Http\Controllers\LaboratoriesController;
use Illuminate\Support\Facades\Route;

Route::apiResource('laboratories', LaboratoriesController::class);
Route::get('paginate-laboratories', [LaboratoriesController::class, 'paginate']);
Route::get('cups-laboratory/{id}', [LaboratoriesController::class, 'cupsLaboratory']);
Route::post('tomar-anular-laboratorio', [LaboratoriesController::class, 'tomarOrAnular']);
Route::post('asignar-tubos', [LaboratoriesController::class, 'asignarTubos']);
Route::post('asignar-horas-laboratorio', [LaboratoriesController::class, 'asignarHoras']);
Route::get('get-all-tubes/{id}', [LaboratoriesController::class, 'getAllTubes']);
Route::get('causal-anulation', [LaboratoriesController::class, 'getCausalAnulation']);
Route::post('document-laboratory', [LaboratoriesController::class, 'cargarDocumento']);
Route::get('download-laboratory/{id}', [LaboratoriesController::class, 'pdf']);
Route::get('laboratory-report', [LaboratoriesController::class, 'report']);
Route::get('delete-document-laboratory/{id}', [LaboratoriesController::class, 'deleteDocument']);
Route::get('download-files-laboratory/{id}', [LaboratoriesController::class, 'downloadFiles']);
Route::get("tube-id/{id}", [LaboratoriesController::class, "getTubeId"]);
