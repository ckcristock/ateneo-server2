<?php

use App\Http\Controllers\ProfessionController;
use Illuminate\Support\Facades\Route;

Route::get('paginateProfessions', [ProfessionController::class, 'paginate']);
Route::apiResource('professions', ProfessionController::class);

