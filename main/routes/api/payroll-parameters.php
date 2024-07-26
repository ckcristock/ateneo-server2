<?php

use App\Http\Controllers\PayrollParametersController;
use Illuminate\Support\Facades\Route;

Route::get('params/payroll/ssecurity_company/percentages/{id}', [PayrollParametersController::class, 'porcentajesSeguridadRiesgos']);
