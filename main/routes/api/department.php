<?php

use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Route;

Route::get('paginateDepartment', [DepartmentController::class, 'paginate']);
Route::apiResource("departments", DepartmentController::class);
