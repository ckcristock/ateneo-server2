<?php

use App\Http\Controllers\TipoServicioController;
use Illuminate\Support\Facades\Route;

Route::apiResource('service-types', TipoServicioController::class);
Route::get('php/configuracion/lista_tipo_servicio.php', [TipoServicioController::class, 'listaTipoServicio']);
Route::get('php/tiposervicios/detalle_tipo_servicio.php', [TipoServicioController::class, 'detalleTipoServicio']);
Route::get('php/contrato/lista_contrato_select.php', [TipoServicioController::class, 'listaContratoSelect']);
Route::get('php/tiposervicios/servicios.php', [TipoServicioController::class, 'servicios']);

Route::post('php/tiposervicios/save_tipo_servicio.php', [TipoServicioController::class, 'saveTipoServicio']);
Route::post('php/tiposervicios/update_tipo_servicio.php', [TipoServicioController::class, 'updateTipoServicio']);
Route::post('php/tiposervicios/cambiar_estado_campo.php', [TipoServicioController::class, 'cambiarEstadoCampo']);
