<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneralController;

Route::get('pruebas-models', [GeneralController::class, 'pruebas']);
Route::get('php/lista_generales.php', [GeneralController::class, 'listaGenerales']);
Route::get('php/genericos/detalle.php', [GeneralController::class, 'detalle']);
Route::get('php/comprobantes/get_codigo.php', [GeneralController::class, 'getCodigo']);
Route::get('actualizar-medicamentos', [GeneralController::class, 'actualizarMedicamentos']);
