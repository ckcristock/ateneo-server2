<?php

use App\Http\Controllers\RegimenTypeController;
use Illuminate\Support\Facades\Route;

Route::get('paginateRegimes', [RegimenTypeController::class, 'paginate']);
Route::get('levels-with-regimes/{id}', [RegimenTypeController::class, 'regimenesConNiveles']);
Route::apiResource("type-regimens", RegimenTypeController::class);
Route::apiResource("regime-type", RegimenTypeController::class);
