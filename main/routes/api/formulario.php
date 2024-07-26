<?php

use App\Http\Controllers\FormularioController;
use Illuminate\Support\Facades\Route;

Route::post("formulario/save-responses", [FormularioController::class, "saveResponse"]);
Route::get("get-formulario/{formulario?}", [FormularioController::class, "getFormulario"]);
