<?php

use App\Http\Controllers\TypeAppointmentController;
use Illuminate\Support\Facades\Route;

Route::get("get-type_appointments/{query?}", [TypeAppointmentController::class, "index"]);
Route::get("type-appointments-paginate", [TypeAppointmentController::class, "paginate"]);
