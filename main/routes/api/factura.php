<?php

use App\Http\Controllers\FacturaController;
use Illuminate\Support\Facades\Route;

Route::get('php/notas_credito_nuevo/get_notas_creditos.php', [FacturaController::class, 'getNotasCreditos']);
Route::get('php/notas_credito_nuevo/lista_facturas_cliente_notas_credito.php', [FacturaController::class, 'listaFacturaClienteNotasCredito']);
Route::get('php/notas_credito_nuevo/lista_producto_notas_credito.php', [FacturaController::class, 'listaProductoNotasCredito']);
Route::get('php/notas_credito_nuevo/get_nota_credito.php', [FacturaController::class, 'getNotaCredito']);
Route::post('php/notas_credito_nuevo/guardar_nota_credito.php', [FacturaController::class, 'guardarNotaCredito']);
Route::get('php/notas_credito_nuevo/get_nota_credito_por_factura.php', [FacturaController::class, 'getNotasCreditoPorFactura']);
