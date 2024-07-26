<?php

use App\Http\Controllers\ExtraHoursController;
use Illuminate\Support\Facades\Route;

Route::get('/horas_extras/turno_rotativo/{fechaInicio}/{fechaFin}/{tipo}', [ExtraHoursController::class, 'getDataRotative'])->where([
    'fechaInicio' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
    'fechaFin' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
]);
Route::post('funcionario/getInfoTotal', [ExtraHoursController::class, 'getInfoTotal']);
Route::post('horas_extras/crear', [ExtraHoursController::class, 'store']);
Route::post('horas_extras/crear-semana', [ExtraHoursController::class, 'storeWeek']);
Route::put('horas_extras/{id}/update', [ExtraHoursController::class, 'update']);
Route::get('horas_extras/datos/validados/{person_id}/{fecha}', [ExtraHoursController::class, 'getDataValid'])->where([
    'fecha' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
]);
