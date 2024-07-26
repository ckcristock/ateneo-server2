<?php

use App\Http\Controllers\AjusteIndividualController;
use Illuminate\Support\Facades\Route;

Route::get('php/ajuste_individual_nuevo/Lista_Ajuste_Individual.php', [AjusteIndividualController::class, 'listaAjusteIndividual']);
Route::get('php/ajuste_individual_nuevo/clase_ajuste_individual.php', [AjusteIndividualController::class, 'claseAjusteIndividual']);
Route::get('php/ajuste_individual_nuevo/lista_productos_entrada.php', [AjusteIndividualController::class, 'listaProductosEntrada']);
Route::get('php/ajuste_individual_nuevo/producto_codigo_barras.php', [AjusteIndividualController::class, 'productoCodigoBarras']);
Route::get('php/ajuste_individual_nuevo/lista_producto_inventario.php', [AjusteIndividualController::class, 'listaProductoInventario']);
Route::post('php/ajuste_individual_nuevo/guardar_salida.php', [AjusteIndividualController::class, 'guardarSalida']);
Route::post('php/ajuste_individual_nuevo/guardar_entrada.php', [AjusteIndividualController::class, 'guardarEntrada']);
Route::get('php/ajuste_individual_nuevo/lista_productos_lotes.php', [AjusteIndividualController::class, 'listaProductosLotes']);
Route::get('php/ajuste_individual_nuevo/actividades_ajuste_individual.php', [AjusteIndividualController::class, 'actividadesAjusteIndividual']);
Route::get('php/ajusteindividual/consultar_bodega_punto.php', [AjusteIndividualController::class, 'consultarBodegaPunto']);
Route::get('php/ajusteindividual/descarga_pdf.php', [AjusteIndividualController::class, 'descargaPDF']);
Route::get('php/ajusteindividual/detalle_ajuste1.php', [AjusteIndividualController::class, 'detalleAjuste1']);
Route::get('php/ajusteindividual/reporte_ajuste_individual.php', [AjusteIndividualController::class, 'reporteAjusteIndividual']);
Route::get('php/contabilidad/movimientoscontables/movimientos_ajuste_individual_pdf.php', [AjusteIndividualController::class, 'movimientosAjusteIndividual']);

