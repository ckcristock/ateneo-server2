<?php

use App\Http\Controllers\PayrollOvertimeController;
use Illuminate\Support\Facades\Route;

Route::get('params/payroll/overtimes/percentages', [PayrollOvertimeController::class, 'horasExtrasPorcentajes']);
