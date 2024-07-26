<?php

use App\Http\Controllers\BalanceGeneralController;
use Illuminate\Support\Facades\Route;

Route::get('php/contabilidad/balancegeneral/descarga_pdf.php', [BalanceGeneralController::class, 'descargaPdf']);
Route::get('php/contabilidad/balancegeneral/descarga_excel.php', [BalanceGeneralController::class, 'descargaExcel']);

