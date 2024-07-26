<?php

use App\Http\Controllers\AuthThirdController;
use Illuminate\Support\Facades\Route;

Route::prefix("auth-third")->group(
    function () {
        Route::post("login", [AuthThirdController::class, "login"]);
        Route::middleware("auth.jwt")->group(function () {
            Route::post("logout", [AuthThirdController::class, "logout"]);
            Route::post("refresh", [AuthThirdController::class, "refresh"]);
            Route::post("me", [AuthThirdController::class, "me"]);
            Route::get("renew", [AuthThirdController::class, "renew"]);
            Route::get("change-password", [AuthThirdController::class, "changePassword"]);
        });
    }
);
