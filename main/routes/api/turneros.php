<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TurnerosController;

Route::get('php/turneros/get_turneros.php', [TurnerosController::class, 'getTurneros']);
Route::get('php/configuracion/lista_turnero.php', [TurnerosController::class, 'listaTurnero']);
Route::get('funcionarios/puntos_funcionario.php', [TurnerosController::class, 'puntosFuncionario']);
Route::get('php/reportes/reporte_atencion_turnero.php', [TurnerosController::class, 'reporteAtencionTurnero']);
Route::get('php/configuracion/detalle_turnero.php', [TurnerosController::class, 'detalleTurnero']);
Route::post('php/configuracion/guardar_turnero.php', [TurnerosController::class, 'guardarTurnero']);
Route::post('php/configuracion/editar_turnero.php', [TurnerosController::class, 'editarTurnero']);
Route::post('php/configuracion/eliminar_turnero.php', [TurnerosController::class, 'eliminarTurnero']);