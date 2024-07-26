<?php

use App\Http\Controllers\DepreciacionController;
use Illuminate\Support\Facades\Route;

Route::get('php/depreciacion/get_depreciaciones.php', [DepreciacionController::class, 'paginate']);
Route::get('php/contabilidad/movimientoscontables/movimientos_depreciacion_pdf.php', [DepreciacionController::class, 'pdf']);
Route::get('php/depreciacion/vista_previa.php', [DepreciacionController::class, 'vistaPrevia']);
Route::post('php/depreciacion/guardar_depreciacion.php', [DepreciacionController::class, 'store']);
