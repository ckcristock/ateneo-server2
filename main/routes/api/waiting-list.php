<?php

use App\Http\Controllers\WaitingListController;
use Illuminate\Support\Facades\Route;

Route::post("cancell-waiting-appointment", [WaitingListController::class, "cancellWaitingAppointment"]);
Route::get("waiting-list-statistics", [WaitingListController::class, "statistics"]);
Route::apiResource("waiting-appointment", WaitingListController::class);
