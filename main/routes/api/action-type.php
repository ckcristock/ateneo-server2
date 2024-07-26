<?php
use App\Http\Controllers\ActionTypeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('action-type', ActionTypeController::class);
Route::get('action-type-paginate', [ActionTypeController::class, 'paginate']);