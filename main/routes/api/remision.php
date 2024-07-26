<?php

use App\Http\Controllers\RemisionController;
use App\Http\Controllers\RemisionNoRotativoController;
use App\Http\Controllers\RemisionRotativoController;
use App\Http\Controllers\RemisionSaveController;
use Illuminate\Support\Facades\Route;

Route::get('php/remision_nuevo/get_datos_iniciales.php', [RemisionController::class, 'datosIniciales']);
Route::get('php/remision_nuevo/get_datos_iniciales_bodegas.php', [RemisionController::class, 'datosBodegasPuntos']);

Route::get('php/alistamiento/detalle_alistamiento.php', [RemisionController::class, 'detalleAlistamiento']);
Route::get('php/alistamiento/guardar_hora_inicio.php', [RemisionController::class, 'guardarHoraInicio']);
Route::get('php/alistamiento/consulta_codigo.php', [RemisionController::class, 'consultaCodigo']);
Route::get('php/alistamiento/productos_remision.php', [RemisionController::class, 'alistamientoProductoRemision']);
Route::post('php/alistamiento/guardar_guia_remisiond.php', [RemisionController::class, 'guardarGuiaRemisiond']);
Route::post('php/alistamiento/guardar_fase1.php', [RemisionController::class, 'guardarFase1Post']);
Route::post('php/alistamiento/guardar_fase2.php', [RemisionController::class, 'guardarFase2Post']);

Route::get('php/alistamiento_nuevo/detalle_fase1.php', [RemisionController::class, 'detalleFase1']);
Route::get('php/alistamiento_nuevo/detalle_fase2.php', [RemisionController::class, 'detalleFase2']);
Route::get('php/alistamiento_nuevo/productos_remision.php', [RemisionController::class, 'productosRemisionAlistamiento']);
Route::get('php/alistamiento_nuevo/consulta_codigo_estiba.php', [RemisionController::class, 'consultaCodigoEstiba']);
Route::post('php/alistamiento_nuevo/guardar_fase1.php', [RemisionController::class, 'guardarFase1']);
Route::get('php/remision_nuevo/remisiones.php', [RemisionController::class, 'remisiones']);
Route::get('php/remision/detalle_tipo.php', [RemisionController::class, 'detalleTipo']);
Route::get('php/remision/remision.php', [RemisionController::class, 'remision']);
Route::get('php/remision/grafica_remisiones.php', [RemisionController::class, 'graficaRemisiones']);
Route::get('php/remision/borradores_remision.php', [RemisionController::class, 'borradoresRemision']);
Route::get('php/remision/productos_remision.php', [RemisionController::class, 'productosRemision']);
Route::get('php/remision/actividades_remision.php', [RemisionController::class, 'actividadesRemision']);
Route::get('php/remision/entrega_pendientes_pdf.php', [RemisionController::class, 'entregaPendientesPDF']);
Route::post('php/remision/get_productos_inventario.php', [RemisionController::class, 'getProductosInventarioPost']);
Route::get('php/archivos/descarga_excel.php', [RemisionController::class, 'descargaExcel']);
Route::get('php/archivos/descarga_zebra.php', [RemisionController::class, 'descargaZebra']);
Route::get('php/archivos/descarga_pdf_price.php', [RemisionController::class, 'descargaPdfPrice']);
Route::get('php/archivos/descarga_pdf.php', [RemisionController::class, 'descargaPdf']);
Route::get('php/remision_nuevo/cupo_cliente.php', [RemisionController::class, 'cupoCliente']);
Route::get('php/remision_nuevo/get_productos_inventario.php', [RemisionController::class, 'getProductosInventario']);
Route::post('php/remision_nuevo/guardar_borrador.php', [RemisionController::class, 'guardarBorrador']);
Route::post('php/remision_nuevo/eliminar_lote_seleccionado.php', [RemisionController::class, 'eliminarLoteSeleccionado']);
Route::get('php/remision_nuevo/comprobar_cantidades.php', [RemisionController::class, 'comprobarCantidades']);
Route::post('php/remision_nuevo/eliminar_lotes_masivos.php', [RemisionController::class, 'eliminarLotesMasivos']);
Route::get('php/remision_nuevo/get_rotativo.php', [RemisionRotativoController::class, 'getRotativos']);
Route::post('php/remision_nuevo/save_remision.php', [RemisionSaveController::class, 'saveRemision']);
Route::get('php/remision_nuevo/get_rotativo_no_pos.php', [RemisionNoRotativoController::class, 'getNoRotativos']);
Route::post('php/remision_nuevo/seleccionar_lotes_inventario.php', [RemisionController::class, 'seleccionarLotesInventario']);



Route::get('php/balanza/estado.php', [RemisionController::class, 'balanza']);
Route::get('php/remision_nuevo/remision.php', [RemisionController::class, 'remisionPhp']);