<?php

use App\Http\Controllers\InventarioFisicoPuntoNuevoController;
use Illuminate\Support\Facades\Route;

Route::get('php/inventariofisicopuntos/estiba/documentos_iniciados.php', [InventarioFisicoPuntoNuevoController::class, 'documentosIniciados']);
Route::get('php/inventariofisicopuntos/estiba/documentos_terminados.php', [InventarioFisicoPuntoNuevoController::class, 'documentosTerminados']);