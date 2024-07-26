<?php

use App\Http\Controllers\BodegasController;
use Illuminate\Support\Facades\Route;

Route::apiResource('bodegas', BodegasController::class);
Route::get('paginateBodegas', [BodegasController::class, 'paginate']);
Route::get('bodegas-with-estibas/{id}', [BodegasController::class, 'bodegasConGrupos']);
Route::get('grupos-with-estibas/{id}', [BodegasController::class, 'gruposConEstibas']);
Route::post('bodegas-activar-inactivar', [BodegasController::class, 'activarInactivar']);
Route::post('grupos-bodegas', [BodegasController::class, 'storeGrupo']);
Route::post('estibas', [BodegasController::class, 'storeEstiba']);
Route::get('impuestos', [BodegasController::class, 'impuestos']);
Route::get('php/bodega_nuevo/get_bodegas.php', [BodegasController::class, 'getBodegas']);
Route::get('php/bodega_nuevo/get_estibas.php', [BodegasController::class, 'getEstibas']);
Route::get('php/bodega/bodega_punto.php', [BodegasController::class, 'bodegaPunto']);
Route::get('php/bodega/vencimientos.php', [BodegasController::class, 'vencimientos']);
Route::get('php/vencimientos/descargar_excel.php', [BodegasController::class, 'descargarExcel']);
Route::get('php/bodega/acta_recepcion_compra_devolucion.php', [BodegasController::class, 'actaRecepcionCompraDevolucion']);
Route::post('php/bodega/guardar_acta_recepcion_devolucion.php', [BodegasController::class, 'guardarActaRecepcionDevolucion']);
Route::get('php/bodega/acta_recepcion_remision.php', [BodegasController::class, 'actaRecepcionRemision']);
Route::get('php/bodega/lista_remisiones_pendientes.php', [BodegasController::class, 'listaRemisionesPendientes']);
Route::get('php/bodega/detalle_acta_recepcion_remision.php', [BodegasController::class, 'detalleActaRecepcionRemision']);

