<?php

use App\Http\Controllers\CierreContableController;
use Illuminate\Support\Facades\Route;

Route::get('php/contabilidad/cierres/lista_cierre.php', [CierreContableController::class, 'listaCierre']);
Route::post('php/contabilidad/cierres/validar_cierre.php', [CierreContableController::class, 'validarCierre']);
Route::post('php/contabilidad/cierres/guardar_cierre.php', [CierreContableController::class, 'guardarCierre']);
Route::get('php/contabilidad/cierres/anular_cierre.php', [CierreContableController::class, 'anularCierre']);
Route::get('php/contabilidad/movimientoscontables/movimientos_cierreanio_excel.php', [CierreContableController::class, 'excel']);
