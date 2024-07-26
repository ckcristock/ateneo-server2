<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovimientoContableController;

Route::get('php/contabilidad/movimientoscontables/movimientos_comprobante_pdf.php', [MovimientoContableController::class, 'movimientosComprobante']);
Route::get('php/contabilidad/movimientoscontables/movimientos_nota_credito_global_pdf.php', [MovimientoContableController::class, 'movimientosNotaCreditoGlobal']);
Route::get('php/contabilidad/movimientoscontables/movimientos_devolucion_compras.php', [MovimientoContableController::class, 'movimientosDevolucionCompras']);
Route::get('php/contabilidad/movimientoscontables/movimientos_acta_recepcion_pdf.php', [MovimientoContableController::class, 'movimientosActaRecepcion']);