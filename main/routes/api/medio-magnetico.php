<?php

use App\Http\Controllers\MedioMagneticoController;
use Illuminate\Support\Facades\Route;

Route::get('php/contabilidad/mediosmagneticos/lista_medios_magneticos.php', [MedioMagneticoController::class, 'lista']);
Route::get('php/contabilidad/mediosmagneticos/detalles.php', [MedioMagneticoController::class, 'detalles']);
Route::get('php/contabilidad/mediosmagneticos/formatos_especiales.php', [MedioMagneticoController::class, 'formatosEspeciales']);
