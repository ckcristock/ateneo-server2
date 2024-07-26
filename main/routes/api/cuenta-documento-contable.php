<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CuentaDocumentoContableController;

Route::get('php/contabilidad/notascarteras/lista_notas_carteras.php', [CuentaDocumentoContableController::class, 'listaNotasCartera']);
Route::get('php/comprobantes/lista_egresos.php', [CuentaDocumentoContableController::class, 'listaEgresos']);
Route::get('php/comprobantes/lista_comprobantes.php', [CuentaDocumentoContableController::class, 'listaComprobantes']);
