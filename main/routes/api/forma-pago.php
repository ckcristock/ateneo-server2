<?php

use App\Http\Controllers\FormaPagoController;
use Illuminate\Support\Facades\Route;

Route::get('php/comprobantes/formas_pago.php', [FormaPagoController::class, 'index']);
