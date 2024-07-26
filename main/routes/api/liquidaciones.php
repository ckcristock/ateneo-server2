<?php

use App\Http\Controllers\LiquidacionesController;
use Illuminate\Support\Facades\Route;

Route::get('nomina/liquidaciones/funcionarios/{id}/mostrar/{fechaFin?}', [LiquidacionesController::class, 'get']);
Route::post('nomina/liquidaciones/{id}/vacaciones_actuales', [LiquidacionesController::class, 'getWithVacacionesActuales']);
Route::post('nomina/liquidaciones/{id}/salario_base', [LiquidacionesController::class, 'getWithSalarioBase']);
Route::post('nomina/liquidaciones/{id}/bases', [LiquidacionesController::class, 'getWithBases']);
Route::post('nomina/liquidaciones/{id}/ingresos', [LiquidacionesController::class, 'getWithIngresos']);
Route::post('nomina/liquidaciones/previsualizacion', [LiquidacionesController::class, 'getPdfLiquidacion']);
Route::get('nomina/liquidaciones/dias-trabajados/{id}/{fechaFin}', [LiquidacionesController::class, 'getDiasTrabajados']);
