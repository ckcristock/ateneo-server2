<?php

use App\Http\Controllers\ListaComprasController;
use Illuminate\Support\Facades\Route;

Route::get('php/rotativoscompras/lista_pre_compra', [ListaComprasController::class, 'preCompras']);
Route::get('php/funcionarios/lista_funcionarios', [ListaComprasController::class, 'getFuncionarios']);
Route::get('php/rotativoscompras/detalle_pre_compra/{id}', [ListaComprasController::class, 'detallePreCompra']);
Route::get('php/comprasnacionales/lista_compras', [ListaComprasController::class, 'paginate']);
Route::get('php/comprasnacionales/datos_compras_nacionales', [ListaComprasController::class, 'datosComprasNacionales']);
Route::get('php/comprasnacionales/detalle_perfil', [ListaComprasController::class, 'detallePerfil']);
Route::get('php/comprasnacionales/detalle_rechazo', [ListaComprasController::class, 'detalleRechazo']);
Route::get('php/comprasnacionales/actividad_orden_compra', [ListaComprasController::class, 'actividadOrdenCompra']);
Route::get('get-estados-compra', [ListaComprasController::class, 'getEstadosCompra']);
Route::post('php/rotativoscompras/actualizar_estado', [ListaComprasController::class, 'actualizarEstadoPreCompra']);
Route::post('php/comprasnacionales/guardar_compra_nacional', [ListaComprasController::class, 'storeCompra']);
Route::post('php/comprasnacionales/actualiza_compra', [ListaComprasController::class, 'setEstadoCompra']);
Route::get("get-product-typeahead-oc", [ListaComprasController::class, 'getProducts']);
Route::get('php/comprasnacionales/descarga_pdf.php/{id}', [ListaComprasController::class, 'descargar']);
Route::get('php/comprasnacionales/proveedor_buscar.php', [ListaComprasController::class, 'proveedorBuscar']);
Route::post('php/comprasnacionales/guardar_compra_nacional_pendientes.php', [ListaComprasController::class, 'guardarCompraNacionalPendientes']);
