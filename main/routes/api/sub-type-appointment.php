<?php

use App\Http\Controllers\SubTypeAppointmentController;
use Illuminate\Support\Facades\Route;

Route::get("sub-type-appointments-paginate", [SubTypeAppointmentController::class, "paginate"]);
Route::get("get-type_subappointments/{query?}", [SubTypeAppointmentController::class, "index"]);
