<?php

use App\Http\Controllers\BenefitsPlanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('benefits_plans', BenefitsPlanController::class);
Route::get('paginateBenefitsPlan', [BenefitsPlanController::class, 'paginate']);
