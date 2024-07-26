<?php

use App\Http\Controllers\PlanCuentasController;
use Illuminate\Support\Facades\Route;

Route::post('import-validator-account-plans/{delete}', [PlanCuentasController::class, 'validateExcel']);
Route::post('import-initial-balances', [PlanCuentasController::class, 'importInitialBalances']);
Route::get('import-commercial-puc', [PlanCuentasController::class, 'importCommercialPuc']);
Route::get('php/plancuentas/lista_plan_cuentas.php', [PlanCuentasController::class, 'paginate']);
Route::get('plan-cuentas-paginacion', [PlanCuentasController::class, 'paginate2']);
Route::get('php/contabilidad/plancuentas/descargar_informe_plan_cuentas_excel.php', [PlanCuentasController::class, 'descargarExcel']);
Route::get('php/contabilidad/plancuentas/detalle_plan_cuenta.php', [PlanCuentasController::class, 'show']);
Route::post('php/contabilidad/plancuentas/cambiar_estado.php', [PlanCuentasController::class, 'cambiarEstado']);
Route::get('php/contabilidad/certificadoretencion/lista_cuentas.php', [PlanCuentasController::class, 'listaCuentas']);
Route::get('php/plancuentas/lista_bancos.php', [PlanCuentasController::class, 'listarBancos']);
Route::post('php/contabilidad/plancuentas/guardar_puc.php', [PlanCuentasController::class, 'store']);
Route::get('php/plancuentas/validar_puc_niveles.php', [PlanCuentasController::class, 'validarNiveles']);
Route::get('php/comprobantes/lista_cuentas.php', [PlanCuentasController::class, 'getListaCuentasContables']);
Route::get('php/plancuentas/filtrar_cuentas.php', [PlanCuentasController::class, 'filtrarCuentas']);
Route::get('php/comprobantes/cuentas.php', [PlanCuentasController::class, 'comprobanteCuentas']);
Route::get('php/plancuentas/get_planes_cuentas.php', [PlanCuentasController::class, 'getPlanCuentas']);
Route::post('php/plancuentas/set_plan_cuentas_tipo_cierre.php', [PlanCuentasController::class, 'setTipoCierre']);

Route::get('php/contabilidad/balanceprueba/lista_cuentas.php', [PlanCuentasController::class, 'balancePruebasListaCuentas']);
Route::get('php/contabilidad/balanceprueba/descarga_pdf.php', [PlanCuentasController::class, 'balancePruebasPdf']);
