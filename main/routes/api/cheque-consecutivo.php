<?php

use App\Http\Controllers\ChequeConsecutivoController;
use Illuminate\Support\Facades\Route;

Route::get('php/comprobantes/lista_cheques.php', [ChequeConsecutivoController::class, 'lista']);