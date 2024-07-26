<?php

use App\Http\Controllers\AccountConfigurationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('account-configurations', AccountConfigurationController::class);
Route::get('account-configurations-paginate', [AccountConfigurationController::class, 'paginate']);
