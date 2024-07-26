<?php

use App\Http\Controllers\DispensacionController;
use Illuminate\Support\Facades\Route;

Route::prefix('/php/dispensaciones')->controller(DispensacionController::class)->group(function () {
    Route::get('lista_dispensaciones.php', 'listaDispensaciones');
    Route::get('get_servicios.php', 'getServicios');
    Route::get('indicadores.php', 'indicadores');
    Route::get('detalle_dispensacion.php', 'detalleDispensacion');
    Route::post('eliminar_dispensacion.php', 'eliminarDispensacion');
    Route::get('dispensacion_pdf.php', 'dispensacionPdf');
    Route::get('productos_pdf.php', 'productosPdf');
    Route::get('descarga_pdf.php', 'descargaPdf');
});