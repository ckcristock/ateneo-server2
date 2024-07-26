<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('file', [FileController::class, 'file']);
Route::get('file-view', [FileController::class, 'fileView']);
