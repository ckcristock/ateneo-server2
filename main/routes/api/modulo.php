<?php
use App\Http\Controllers\ModuloController;
use Illuminate\Support\Facades\Route;

Route::get('php/contabilidad/tipos_documentos.php', [ModuloController::class, 'index']);
