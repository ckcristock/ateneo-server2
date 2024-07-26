<?php

use App\Http\Controllers\VisaTypeController;
use Illuminate\Support\Facades\Route;

Route::get('paginateVisaTypes', [VisaTypeController::class, 'paginate']);
Route::apiResource('visa-types', VisaTypeController::class);
