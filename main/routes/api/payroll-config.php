<?php

use App\Http\Controllers\PayrollConfigController;
use Illuminate\Support\Facades\Route;

Route::get('parametrizacion/nomina/all', [PayrollConfigController::class, 'getParametrosNomina']);
Route::get('parametrizacion/nomina/extras', [PayrollConfigController::class, 'horasExtrasDatos']);
Route::get('parametrizacion/nomina/incapacidades', [PayrollConfigController::class, 'incapacidadesDatos']);
Route::get('parametrizacion/nomina/novelties', [PayrollConfigController::class, 'novedadesList']);
Route::get('parametrizacion/nomina/parafiscales', [PayrollConfigController::class, 'parafiscalesDatos']);
Route::get('parametrizacion/nomina/riesgos', [PayrollConfigController::class, 'riesgosArlDatos']);
Route::get('parametrizacion/nomina/ssocial_empresa', [PayrollConfigController::class, 'sSocialEmpresaDatos']);
Route::get('parametrizacion/nomina/ssocial_funcionario', [PayrollConfigController::class, 'sSocialFuncionarioDatos']);
Route::get('parametrizacion/nomina/income', [PayrollConfigController::class, 'incomeDatos']);
Route::get('parametrizacion/nomina/deductions', [PayrollConfigController::class, 'deductionsDatos']);
Route::get('parametrizacion/nomina/liquidations', [PayrollConfigController::class, 'liquidationsDatos']);
Route::get('parametrizacion/nomina/salarios-subsidios', [PayrollConfigController::class, 'SalariosSubsidiosDatos']);
Route::put('parametrizacion/nomina/extras/update/{id}', [PayrollConfigController::class, 'horasExtrasUpdate']);
Route::put('parametrizacion/nomina/seguridad-social-persona/update/{id}', [PayrollConfigController::class, 'sSocialPerson']);
Route::put('parametrizacion/nomina/seguridad-social-company/update/{id}', [PayrollConfigController::class, 'sSocialCompany']);
Route::put('parametrizacion/nomina/riesgos-arl/update/{id}', [PayrollConfigController::class, 'riesgosArlUpdate']);
Route::put('parametrizacion/nomina/parafiscales/update/{id}', [PayrollConfigController::class, 'parafiscalesUpdate']);
Route::put('parametrizacion/nomina/novedades/update/{id}', [PayrollConfigController::class, 'novedadesUpdate']);
Route::put('parametrizacion/nomina/incapacidades/update/{id}', [PayrollConfigController::class, 'incapacidadesUpdate']);
Route::post('parametrizacion/nomina/income/update', [PayrollConfigController::class, 'createUptadeIncomeDatos']);
Route::post('parametrizacion/nomina/deductions/update', [PayrollConfigController::class, 'createUpdateDeductionsDatos']);
Route::post('parametrizacion/nomina/liquidations/update', [PayrollConfigController::class, 'createUpdateLiquidationsDatos']);
Route::post('parametrizacion/nomina/salarios-subsidios/update', [PayrollConfigController::class, 'createUpdateSalariosSubsidiosDatos']);

Route::get('payroll-managers', [PayrollConfigController::class, 'payrollManagers']);
