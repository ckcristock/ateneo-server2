<?php

use App\Http\Controllers\AgendamientoController;
use Illuminate\Support\Facades\Route;

Route::apiResource("agendamientos", AgendamientoController::class);

Route::controller(AgendamientoController::class)->group(function () {
    Route::post("agendamientos-cancel", "cancel");
    Route::post("cancell-agenda", "cancellAgenda");
    Route::get("agendamientos-paginate", "indexPaginate");
    Route::get('agendamientos-detail/{id}', "showDetail");
});
