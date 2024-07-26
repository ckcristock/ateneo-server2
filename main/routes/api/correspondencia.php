<?php

use App\Http\Controllers\CorrespondenciaController;
use Illuminate\Support\Facades\Route;

Route::prefix('/php/correspondencia')->controller(CorrespondenciaController::class)->group(function () {
    Route::get('lista_correspondencia.php', 'listaCorrespondencia');
});