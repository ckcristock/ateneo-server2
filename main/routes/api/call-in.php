<?php

use App\Http\Controllers\CallInController;
use Illuminate\Support\Facades\Route;

Route::post("presentianCall", [CallInController::class, "presentialCall"]);
Route::post("get-call-by-identifier", [CallInController::class, "getCallByIdentifier"]);
Route::post("patientforwaitinglist", [CallInController::class, "patientforwaitinglist"]);
