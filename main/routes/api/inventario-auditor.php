<?php
use App\Http\Controllers\InventarioAuditorController;
use Illuminate\Support\Facades\Route;


Route::get('php/inventario_auditor/inventario.php', [InventarioAuditorController::class, 'inventario']);
Route::get('php/inventario_auditor/saveconteo_custom.php', [InventarioAuditorController::class, 'saveconteoCustom']);
Route::get('php/inventario_auditor/documentos_para_ajustar_auditables.php', [InventarioAuditorController::class, 'documentosParaAjustarAuditables']);
Route::get('php/inventario_auditor/guardar_inventario_final.php', [InventarioAuditorController::class, 'guardarInventarioFinal']);
Route::get('php/inventario_auditor/reconteo.php', [InventarioAuditorController::class, 'reconteo']);
Route::get('php/inventario_auditor/save_reconteo.php', [InventarioAuditorController::class, 'saveReconteo']);