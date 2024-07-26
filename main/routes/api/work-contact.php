<?php

use App\Http\Controllers\WorkContractController;
use Illuminate\Support\Facades\Route;

Route::apiResource('work_contracts', WorkContractController::class);
Route::post('enterpriseData', [WorkContractController::class, 'updateEnterpriseData']);
Route::post('finish-contract', [WorkContractController::class, 'finishContract']);
Route::get('contractsToExpire', [WorkContractController::class, 'contractsToExpire']);
Route::get('contractRenewal/{id}', [WorkContractController::class, 'contractRenewal']);
Route::get('preLiquidado', [WorkContractController::class, 'getPreliquidated']);
Route::get('liquidado/{id}', [WorkContractController::class, 'getLiquidated']);
Route::get('periodoP', [WorkContractController::class, 'getTrialPeriod']);
Route::get('get-work-contracts-list/{id}', [WorkContractController::class, 'getWorkContractsList']);
Route::get('download-work-contracts/{id}', [WorkContractController::class, 'pdf']);
Route::get('get-turn-types', [WorkContractController::class, 'getTurnTypes']);
