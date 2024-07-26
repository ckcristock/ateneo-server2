<?php

use App\Http\Controllers\TipoActivoFijoController;
use Illuminate\Support\Facades\Route;

Route::get('php/tipoactivo/get_tipo_activos.php', [TipoActivoFijoController::class, 'index']);
Route::get('php/tipoactivo/get_lista_tipo_activo.php', [TipoActivoFijoController::class, 'paginate']);
Route::post('php/tipoactivo/guardar_tipo_activo.php', [TipoActivoFijoController::class, 'store']);
