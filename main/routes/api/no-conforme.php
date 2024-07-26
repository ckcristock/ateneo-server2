<?php

use App\Http\Controllers\NoConformeController;
use Illuminate\Support\Facades\Route;

Route::get('php/noconforme/devoluciones.php', [NoConformeController::class, 'devoluciones']);
Route::get('php/noconforme/lista_no_conforme_compra.php', [NoConformeController::class, 'listaNoConformeCompra']);
Route::get('php/noconforme/lista_no_conforme_compra_d.php', [NoConformeController::class, 'listaNoConformeCompraD']);
Route::get('php/noconforme/cerrar_no_conforme.php', [NoConformeController::class, 'cerrarNoConforme']);
Route::get('php/noconforme/Vista_Principal.php', [NoConformeController::class, 'vistaPrincpal']);
Route::get('php/noconforme/ver_no_conforme.php', [NoConformeController::class, 'verNoConforme']);
Route::get('php/noconforme/actividades_devolucion.php', [NoConformeController::class, 'actividadesDevolucion']);
Route::get('php/noconforme/cargar_actas_recepcion.php', [NoConformeController::class, 'cargarActasRecepcion']);
Route::get('php/noconforme/lista_productos.php', [NoConformeController::class, 'listaProductos']);
Route::get('php/noconforme/cargar_facturas_actas_recepcion.php', [NoConformeController::class, 'cargarFacturasActasRecepcion']);

Route::get('php/devoluciones/detalle_devolucion.php', [NoConformeController::class, 'detalleDevolucion']);

Route::post('php/noconforme/anular_devolucion.php', [NoConformeController::class, 'anularDevolucion']);
Route::post('php/noconforme/devolucion_producto_no_conforme.php', [NoConformeController::class, 'devolucionProductoNoConforme']);

Route::get('php/noconforme/descargar_pdf.php', [NoConformeController::class, 'descargarPdf']);