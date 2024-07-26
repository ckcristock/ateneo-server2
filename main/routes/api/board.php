<?php

use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

Route::get("board", [BoardController::class, "getData"]);
