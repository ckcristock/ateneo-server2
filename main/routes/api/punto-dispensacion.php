<?php

use App\Http\Controllers\PuntoDispensacionController;
use Illuminate\Support\Facades\Route;

Route::get("php/entregapendientes/get_puntos.php", [PuntoDispensacionController::class, "getPuntos"]);
Route::get("php/servicio/servicios_ng_select.php", [PuntoDispensacionController::class, "serviciosNgSelect"]);
Route::get("php/puntodispensacion/detalle_punto_dispensacion.php", [PuntoDispensacionController::class, "detallePuntoDispensacion"]);
Route::get("php/despacho/get_bodegas.php", [PuntoDispensacionController::class, "getBodegas"]);
Route::get("php/puntodispensacion/get_detalle_punto_dispensacion.php", [PuntoDispensacionController::class, "getDetalleDispensacion"]);
Route::get("php/GENERALES/tiposervicio/get_tipos_servicio_ng_select.php", [PuntoDispensacionController::class, "getTiposServicioNgSelect"]);
Route::get("php/GENERALES/puntos/lista_puntos_dispensacion.php", [PuntoDispensacionController::class, "listaPuntosDispensacion"]);
Route::post("php/genericos/guardar_generico.php", [PuntoDispensacionController::class, "guardarGenerico"]);
Route::post("editar_punto_dispensacion.php", [PuntoDispensacionController::class, "updatePuntoDispensacion"]);
Route::post("guardar_punto_dispensacion.php", [PuntoDispensacionController::class, "savePuntoDispensacion"]);
