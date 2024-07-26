<?php

use App\Http\Controllers\CategoriaNuevaController;
use Illuminate\Support\Facades\Route;

Route::get('php/categoria_nueva/detalle_categoria_nueva_general.php', [CategoriaNuevaController::class, 'index']);
Route::get('php/genericos/departamentos.php', [CategoriaNuevaController::class, 'getDepartamentos']);
Route::get('php/categoria_nueva/detalle_categoria_nueva_departamento.php', [CategoriaNuevaController::class, 'categoriaDepartamento']);
