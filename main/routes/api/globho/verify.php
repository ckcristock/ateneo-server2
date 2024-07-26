<?php

use App\Http\Controllers\ServiceGlobhoController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::group(["middleware" => ["globho.verify"]], function () {
    Route::post('create-professional', [PersonController::class, "storeFromGlobho"]);
    Route::put('professional', [PersonController::class, "updateFromGlobho"]);
    Route::post('update-appointment-by-globho', [ServiceGlobhoController::class, 'updateStateByGlobhoId']);
    Route::get("get-appointments-by-globho-id", [ServiceGlobhoController::class, "getInfoByGlobhoId"]);
    Route::post('create-appoinment', [AppointmentController::class, 'createFromGlobho']);
});
