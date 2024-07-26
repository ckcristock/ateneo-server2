<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('image', [ImageController::class, 'image']);
Route::get('optimize-image-people', [ImageController::class, 'optimize']);
