<?php

use App\Http\Controllers\ThirdPartyPersonController;
use Illuminate\Support\Facades\Route;

Route::apiResource('third-party-person', ThirdPartyPersonController::class);
Route::get('third-party-person-for-third/{id}', [ThirdPartyPersonController::class, 'getThirdPartyPersonForThird']);
Route::get('third-party-person-index', [ThirdPartyPersonController::class, 'getThirdPartyPersonIndex']);
