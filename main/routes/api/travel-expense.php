<?php

use App\Http\Controllers\TravelExpenseController;
use Illuminate\Support\Facades\Route;

Route::post('travel-expense/update/{id}', [TravelExpenseController::class, 'update']);
Route::get('travel-expense/pdf/{id}/{company_id}', [TravelExpenseController::class, 'pdf']);
Route::apiResource('travel-expense', TravelExpenseController::class);
Route::post('approve/{id}', [TravelExpenseController::class, 'approve']);
