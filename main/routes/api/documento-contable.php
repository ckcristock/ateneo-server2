<?php

use App\Http\Controllers\DocumentoContableController;
use Illuminate\Support\Facades\Route;

Route::get('php/contabilidad/notascontables/lista_notas_contables.php', [DocumentoContableController::class, 'paginate']);
Route::get('php/contabilidad/notascontables/nit_buscar.php', [DocumentoContableController::class, 'nitBuscar']);
Route::get('php/contabilidad/notascontables/get_codigo.php', [DocumentoContableController::class, 'getCodigo']);
Route::get('php/contabilidad/notascontables/descarga_pdf.php', [DocumentoContableController::class, 'descargarPdf']);
Route::post('php/contabilidad/notascontables/guardar_nota.php', [DocumentoContableController::class, 'guardarNota']);
Route::post('php/contabilidad/notascontables/subir_facturas.php', [DocumentoContableController::class, 'subirFacturas']);


