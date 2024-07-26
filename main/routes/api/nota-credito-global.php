<?php

use App\Http\Controllers\NotaCreditoGlobalController;
use Illuminate\Support\Facades\Route;

Route::get('php/notas_credito_nuevo/descarga_pdf.php', [NotaCreditoGlobalController::class, 'descargaPdf']);