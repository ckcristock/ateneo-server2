<?php
use App\Http\Controllers\DisciplinaryClosureReasonController;
use Illuminate\Support\Facades\Route;

Route::apiResource('disciplinary-closure-reason', DisciplinaryClosureReasonController::class);
Route::get('disciplinary-closure-reason-paginate', [DisciplinaryClosureReasonController::class, 'paginate']);