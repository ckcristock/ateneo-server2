<?php

use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollPaymentController;
use Illuminate\Support\Facades\Route;

Route::get("payroll-nex-mouths", [PayrollController::class, "nextMonths"]);
Route::get('nomina/pago/funcionario/{identidad}', [PayrollController::class, 'getFuncionario']);
Route::get('nomina/pago/funcionarios/{inicio?}/{fin?}', [PayrollController::class, 'payPeople']);
Route::get('nomina/pago/{inicio?}/{fin?}', [PayrollController::class, 'getPayrollPay']);
Route::post('download-payroll', [PayrollController::class, 'downloadExcelNomina']);
Route::get('download-disabilities/{inicio}/{fin}', [PayrollController::class, 'downloadExcelNovedades']);
Route::get('payroll/overtimes/person/{id}/{dateStart}/{dateEnd}', [PayrollController::class, 'getExtrasTotales']);
Route::get('payroll/salary/person/{id}/{dateStart}/{dateEnd}', [PayrollController::class, 'getSalario']);
Route::get('payroll/factors/person/{id}/{dateStart}/{dateEnd}', [PayrollController::class, 'getNovedades']);
Route::get('payroll/incomes/person/{id}/{fechaInicio}/{fechaFin}', [PayrollController::class, 'getIngresos']);
Route::get('payroll/retentions/person/{id}/{fechaInicio}/{fechaFin}', [PayrollController::class, 'getRetenciones']);
Route::get('payroll/deductions/person/{id}/{fechaInicio}/{fechaFin}', [PayrollController::class, 'getDeducciones']);
Route::get('payroll/net-pay/person/{id}/{fechaInicio}/{fechaFin}', [PayrollController::class, 'getPagoNeto']);
Route::get('payroll/social-security/person', [PayrollController::class, 'getPorcentajes']);
Route::get('payroll/history/payments', [PayrollPaymentController::class, 'getPagosNomina']);
Route::get('payroll/security/person/{id}/{fechaInicio}/{fechaFin}', [PayrollController::class, 'getSeguridad']);
Route::get('payroll/provisions/person/{id}/{fechaInicio}/{fechaFin}', [PayrollController::class, 'getProvisiones']);
Route::post('payroll/pay', [PayrollController::class, 'store']);
Route::post('payroll/report/{id}', [PayrollController::class, 'reportDian']);
Route::post('payroll/report-electronic/{id}/{idPersonPayroll?}', [PayrollController::class, 'reportDian']);
Route::post('nomina/get-colillas', [PayrollController::class, 'getPdfsNomina']);
Route::post('nomina/enviar-colillas', [PayrollController::class, 'sendPayrollEmail']);
/* 	Route::get('payroll/social-security/person/{id}/{fechaInicio}/{fechaFin}', [PayrollController::class, 'getPorcentajes']); */
//Route::get('download-payroll', [PayrollController::class, 'downloadPdf']);

/* NÓMINA ELECTRÓNICA */
Route::post('payroll/dian-report', [PayrollController::class, 'dianReport']);
Route::get('payroll/details-dian-report/{id}', [PayrollController::class, 'detailsDianReport']);
