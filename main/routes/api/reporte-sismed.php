<?php

use App\Http\Controllers\ReporteSismedController;
use Illuminate\Support\Facades\Route;

Route::get('php/reporte_sismed/reporte_sismed_compra.php', [ReporteSismedController::class, 'reporteSismedCompra']);
Route::get('php/reporte_sismed/reporte_sismed.php', [ReporteSismedController::class, 'reporteSismed']);
Route::get('php/reporte_sismed/reporte_sismed_plano_compra.php', [ReporteSismedController::class, 'reporteSismedPlanoCompra']);
Route::get('php/reporte_sismed/reporte_sismed_plano.php', [ReporteSismedController::class, 'reporteSismedPlano']);
Route::get('php/reporte_sismed/reporte_ventas.php', [ReporteSismedController::class, 'reporteVentas']);