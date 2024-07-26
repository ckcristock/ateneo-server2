<?php

use App\Http\Controllers\ReporteHorariosController;
use Illuminate\Support\Facades\Route;

Route::get('reporte/horarios/{fechaInicio}/{fechaFin}/turno_fijo', [ReporteHorariosController::class, 'fixedTurnDiaries'])->where([
    'fechaInicio' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
    'fechaFin' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
]);
Route::get('download/horarios/{fechaInicio}/{fechaFin}', [ReporteHorariosController::class, 'download'])->where([
    'fechaInicio' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
    'fechaFin' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
]);

//Route::get('listado-horarios', [ReporteHorariosController::class, 'pruebaPrueba']); //eliminar esta ruta
/* Route::get('/reporte/horarios/{fechaInicio}/{fechaFin}/turno_rotativo', [ReporteHorariosController::class, 'getDatosTurnoRotativo'])->where([
    'fechaInicio' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
    'fechaFin'    => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
]); */
