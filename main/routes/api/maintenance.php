<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaintenanceController;

Route::get('generate-users', [MaintenanceController::class, 'generateUsers']);
Route::get('run-commands', [MaintenanceController::class, 'runCommands']);
