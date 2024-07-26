<?php

use App\Http\Controllers\DiaryEditController;
use Illuminate\Support\Facades\Route;

Route::post("update-hours-worked", [DiaryEditController::class, 'updateDiary']);
