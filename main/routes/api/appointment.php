<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::post("cancel-appointment/{id}", [AppointmentController::class, "cancel"]);
Route::post("confirm-appointment", [AppointmentController::class, "confirmAppointment"]);
Route::post("appointment-recursive", [AppointmentController::class, "appointmentRecursive"]);
Route::post("migrate-appointment", [AppointmentController::class, "appointmentMigrate"]);
Route::get("appointments/tomigrate", [AppointmentController::class, "toMigrate"]);
Route::get("appointments-pending", [AppointmentController::class, "getPending"]);
Route::get("get-statistics-by-collection", [AppointmentController::class, "getstatisticsByCollection"]);
Route::get("clean-info/{id?}", [AppointmentController::class, "cleanInfo"]);
Route::get("clean-info", [AppointmentController::class, "getDataCita"]);
Route::apiResource("appointments", AppointmentController::class);
