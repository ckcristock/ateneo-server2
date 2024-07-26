<?php
use App\Http\Controllers\OrdenCompraNacionalController;
use Illuminate\Support\Facades\Route;

Route::get('php/bodega_nuevo/lista_compras_pendientes.php', [OrdenCompraNacionalController::class, 'listarPendientes']);
Route::get('php/bodega_nuevo/acta_recepcion_comprad_test.php', [OrdenCompraNacionalController::class, 'actaRecepcionCompra']);
Route::get('php/actarecepcion/codigo_barrad.php', [OrdenCompraNacionalController::class, 'codigoBarras']);
