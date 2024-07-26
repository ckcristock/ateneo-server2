<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkContractTypeController;

Route::get('paginateContractType', [WorkContractTypeController::class, 'paginate']);
Route::apiResource('work-contract-type', WorkContractTypeController::class);
Route::get('work-contract-type-list', [WorkContractTypeController::class, 'getWorkContractTypeList']);
