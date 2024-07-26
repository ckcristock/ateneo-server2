<?php

use App\Http\Controllers\BonusController;
use Illuminate\Support\Facades\Route;

Route::apiResource('bonuses', BonusController::class);
Route::get('check-bonuses/{period}', [BonusController::class, 'checkBonuses']);
Route::get('bonuses-report/{anio}/{period}/{pagado}', [BonusController::class, 'reportBonus']);
Route::get('bonus-stubs/{anio}/{period}', [BonusController::class, 'pdfGenerate']);
Route::get('paginate-bonuses', [BonusController::class, 'paginate']);
Route::post('query-bonuses', [BonusController::class, 'consultaPrima']);
