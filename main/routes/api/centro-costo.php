<?php

use App\Http\Controllers\CentroCostoController;
use Illuminate\Support\Facades\Route;

/* Centro costos */

Route::get('php/centroscostos/lista_centros_costos.php', [CentroCostoController::class, 'paginate']);
Route::get('php/centroscostos/lista_tipo_centro.php', [CentroCostoController::class, 'listaTipo']);
Route::get('php/centroscostos/listar_valores_tipo_centro.php', [CentroCostoController::class, 'listaValores']);
Route::get('php/centroscostos/consultar_centro_costo.php', [CentroCostoController::class, 'consultarCentro']);
Route::get('php/centroscostos/cambiar_estado_centro_costo.php', [CentroCostoController::class, 'cambiarCentro']);
Route::post('php/centroscostos/guardar_centros_costos.php', [CentroCostoController::class, 'store']);
Route::get('php/centroscostos/exportar.php', [CentroCostoController::class, 'exportar']);
Route::get('php/contabilidad/notascontables/centrocosto_buscar.php', [CentroCostoController::class, 'buscar']);
Route::get('php/contabilidad/notascarteras/centrocosto_buscar.php', [CentroCostoController::class, 'buscar']);
Route::get('php/contabilidad/balanceprueba/lista_centro_costos.php', [CentroCostoController::class, 'listaCentro']);
