<?php

use App\Http\Controllers\ActivoFijoController;
use Illuminate\Support\Facades\Route;

Route::controller(ActivoFijoController::class)->group(function () {
    Route::prefix('php/activofijo')->group(function () {
        Route::get('get_lista_activo_fijo.php', 'paginate');
        Route::get('datos_reporte.php', 'datosReporte');
        Route::get('get_detalle_activo_fijo.php', 'show');
        Route::get('adiciones_activo.php', 'adiciones');
        Route::get('get_codigo.php', 'getCodigo');
        Route::get('cuentas_retenciones.php', 'cuentasRetenciones');
        Route::get('cuentas.php', 'cuentas');
        Route::get('filtrar.php', 'filtrar');
        Route::get('lista_facturas.php', 'listaFacturas');
        Route::get('get_activo_fijo_adiccion.php', 'adicion');
        Route::get('reportes.php', 'reportes');
        Route::post('guardar_activo_fijo.php', 'store');
        Route::post('guardar_activo_fijo_adicion.php', 'guardarAdicion');
    });
    Route::get('php/contabilidad/movimientoscontables/movimientos_activo_fijo_pdf.php', 'pdf');
    Route::post('php/contabilidad/anular_documento.php', 'anularDocumento');
});


