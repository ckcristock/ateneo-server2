<?php

use App\Http\Controllers\BorradorContabilidadController;
use Illuminate\Support\Facades\Route;

Route::get('php/contabilidad/lista_borrador_contable.php', [BorradorContabilidadController::class, 'lista']);
Route::get('php/contabilidad/detalles_borrador_contable.php', [BorradorContabilidadController::class, 'detalles']);
Route::post('php/contabilidad/guardar_borrador_contable.php', [BorradorContabilidadController::class, 'guardar']);