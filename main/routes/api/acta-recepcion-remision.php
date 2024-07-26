<?php

use App\Http\Controllers\ActaRecepcionRemisionController;
use Illuminate\Support\Facades\Route;

Route::get('php/actarecepcion/lista_actarecepcion_remisiones.php', [ActaRecepcionRemisionController::class, 'indexPaginate']);
