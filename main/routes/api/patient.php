<?php

use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::get("get-patient-fill/{id}", [PatientController::class, "getPatientResend"]);

Route::get("get-patient", [PatientController::class, "getPatientInCall"]);

Route::apiResource("patients", PatientController::class);

Route::get("patients-paginate", [PatientController::class, 'paginate']);

Route::get("patients-chart", [PatientController::class, 'charts']);

Route::post("patients-import", [PatientController::class, 'import']);

Route::get('download-template-imports-patients', [PatientController::class, 'exportTemplate']);

Route::get('php/actarecepcion/lista_impuesto_mes.php', [PatientController::class, 'listaImpuestoMes']);

Route::post('php/genericos/eliminar_generico.php', [PatientController::class, 'eliminarGenerico']);