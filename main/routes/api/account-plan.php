<?php

use App\Http\Controllers\AccountPlanController;
use Illuminate\Support\Facades\Route;

Route::controller(AccountPlanController::class)->group(function () {
    Route::get('account-plan', 'accountPlan');
    Route::get('account-plan-balance', 'listBalance');
    Route::get('account-plan-list', 'list');
    Route::get('account-plan-select', 'select');
});
