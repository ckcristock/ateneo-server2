<?php

use App\Http\Controllers\ThirdPartyFieldController;
use Illuminate\Support\Facades\Route;

Route::apiResource('third-party-fields', ThirdPartyFieldController::class);
Route::put('changeStateField/{id}', [ThirdPartyFieldController::class, 'changeState']);
