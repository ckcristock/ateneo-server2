<?php

use App\Http\Controllers\ComprobanteController;
use Illuminate\Support\Facades\Route;

Route::get('php/comprobantes/detalle_comprobante.php', [ComprobanteController::class, 'detalleComprobante']);
Route::post('php/comprobantes/guardar_comprobante.php', [ComprobanteController::class, 'guardarComprobante']);
Route::get('php/comprobantes/egreso_descarga_pdf.php', [ComprobanteController::class, 'egresoDescargaPdf']);
Route::get('php/comprobantes/comprobantes_pdf.php', [ComprobanteController::class, 'comprobantesPdf']);
Route::get('php/comprobantes/lista_facturas_clientes.php', [ComprobanteController::class, 'listaFacturaClientes']);