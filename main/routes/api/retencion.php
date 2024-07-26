<?php

use App\Http\Controllers\RetencionController;
use Illuminate\Support\Facades\Route;

Route::get('php/activofijo/retenciones.php', [RetencionController::class, 'index']);
Route::get('php/contabilidad/lista_retenciones.php', [RetencionController::class, 'lista']);
Route::get('php/GENERALES/retenciones/get_retenciones_modalidad.php', [RetencionController::class, 'getRetencionesModalidad']);
