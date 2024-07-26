<?php

use App\Http\Controllers\ReportekardexController;
use Illuminate\Support\Facades\Route;

Route::get('php/reportekardex/lista_productos.php', [ReportekardexController::class, 'listaProductos']);
Route::get('php/reportekardex/bodega_punto.php', [ReportekardexController::class, 'bodegaPunto']);
Route::get('php/reportekardex/consulta_kardexd.php', [ReportekardexController::class, 'consultaKardexd']);
Route::get('php/archivos/descarga_kardexd.php', [ReportekardexController::class, 'descargaKardexd']);
Route::get('php/facturasventas/detalle_factura_venta.php', [ReportekardexController::class, 'detalleFacturaVenta']);
Route::get('php/contabilidad/movimientoscontables/movimientos_factura_venta_pdf.php', [ReportekardexController::class, 'movimientosFacturaVentaPdf']);
Route::get('php/notas_credito_nuevo/get_nota_credito_por_factura.php', [ReportekardexController::class, 'getNotaCreditoPorFactura']);
Route::get('php/facturasventas/descarga_pdf.php', [ReportekardexController::class, 'descargaPdf']);
Route::get('php/remision', [ReportekardexController::class, 'remision']);
Route::get('php/inventario_fisico_puntos/descarga_pdf.php', [ReportekardexController::class, 'inventarioFiscoDescargaPdf']);