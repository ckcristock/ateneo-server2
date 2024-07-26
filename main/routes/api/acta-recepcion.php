<?php

use App\Http\Controllers\ActaRecepcionController;
use App\Http\Controllers\CausalNoConformeController;
use Illuminate\Support\Facades\Route;

Route::controller(ActaRecepcionController::class)->group(function () {
    Route::prefix('php/actarecepcion_nuevo')->group(function () {
        Route::get('lista_actas_pendientes.php', 'listarPendientes');
        Route::get('lista_actarecepcion.php', 'listarActas');
        Route::post('aprobar_acta.php', 'aprobarActa');
        Route::get('lista_subcategorias.php', 'listaSubcategorias');
        Route::get('lista_impuesto_mes.php', 'listaImpuestoMes');
        Route::get('lista_impuesto_mes2.php', 'listaImpuestoMes2');
        Route::post('acomodar_acta.php', 'acomodarActa');
    });

    Route::prefix('php/actarecepcion')->group(function () {
        Route::get('actividades_acta_recepcion_compra.php', 'getActividadesActa');
        Route::get('lista_acta_anula.php', 'listarAnuladas');
        Route::post('anular_acta.php', 'anularActa');
        Route::get('descarga_pdf.php', 'descargarPdf');
        Route::get('descarga_pdf_acta_remision.php', 'descargarPdfActaRemision');
        Route::get('causal_no_conformes.php', 'listaCausalesNoConforme');
        Route::post('guardar_acta_remisones_pendientes.php', 'guardarActaRemisionesPendientes');
    });

    Route::prefix('php/bodega_nuevo')->group(function () {
        Route::get('detalle_acta_recepcion.php', 'detalleActa');
        Route::post('guardar_acta_recepciond.php', 'save');
        Route::get('detalle_acta_recepcion_acomodar.php', 'detalleAcomodar');
        Route::get('validar_bodega_en_inventario.php', 'validarBodegaInventario');
        Route::get('validar_estiba.php', 'validarEstiba');

    });

    Route::resource('php/actarecepcion/causalnoconformes', CausalNoConformeController::class);

    Route::get('php/facturasventas/causales_anulacion.php', 'indexCausalAnulacion');
    Route::get('php/facturasventas/detalle_factura_venta.php', 'detalleFacturaVenta');
    Route::get('php/facturasventas/descarga_pdf.php', 'descargaPdf');
    Route::get('validate-acta-history/{id}', 'validateActa');

    Route::get('php/actarecepcion_nuevo/lista_actarecepcion.php', 'listaActaRecepcion');
    Route::get('php/actarecepcion/detalle_perfil_dev.php', 'detallePerfil');

});
