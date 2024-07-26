<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EgresoController;

Route::post('php/comprobantes/guardar_egreso.php', [EgresoController::class, 'guardar']);
Route::get('php/contabilidad/notascontables/lista_facturas.php', [EgresoController::class, 'listaFacturas']);