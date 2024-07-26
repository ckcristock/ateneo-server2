<?php
use App\Http\Controllers\DisabilityLeaveController;
use Illuminate\Support\Facades\Route;

Route::apiResource('disability-leaves', DisabilityLeaveController::class);
Route::get('paginateNoveltyTypes', [DisabilityLeaveController::class, 'paginate']);
