<?php

use App\Http\Controllers\ReasonWithdrawalController;
use Illuminate\Support\Facades\Route;

Route::apiResource('reason_withdrawal', ReasonWithdrawalController::class);
