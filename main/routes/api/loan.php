<?php

use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('loan', LoanController::class);
Route::get('proyeccion_pdf/{id}/{company_id}', [LoanController::class, 'loanpdf']);
Route::get('proyeccion_excel/{id}/{company_id}', [LoanController::class, 'loanExcel']);
Route::get('loan-paginate', [LoanController::class, 'paginate']);
Route::get('php/prestamoylibranza/comprobar_prestamo.php', [LoanController::class, 'comprobarPrestamo']);
Route::get('php/prestamoylibranza/pazysalvo.php/{id}/{company_id}', [LoanController::class, 'pazSalvo']);
