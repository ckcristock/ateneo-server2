<?php

use App\Http\Controllers\RetentionTypeController;
use Illuminate\Support\Facades\Route;

Route::get('paginateRetentionType', [RetentionTypeController::class, 'paginate']);
Route::apiResource('retention-type', RetentionTypeController::class);
Route::get('retention-type-select',[RetentionTypeController::class,  'select']);
