<?php

use App\Http\Controllers\CaracterizacionController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["jwt.verify"]], function () {
    Route::controller(CaracterizacionController::class)->group(function () {
        Route::prefix('caracterizacion')->group(function () {
            Route::get('pacientesedadsexo', 'PacienteEdadSexo');
            Route::get('pacientespatologiasexo', 'PacientePatologiaSexo');
        });
        Route::get("/pacientes/listapacientes", "ListaPacientes");
    });

});
