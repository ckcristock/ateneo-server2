<?php
use App\Http\Controllers\InventarioNuevoController;
use Illuminate\Support\Facades\Route;

Route::get('php/inventario_nuevo/lista_inventario.php', [InventarioNuevoController::class, 'listar']);

Route::get('php/inventariofisico/estiba/iniciar_inventario.php', [InventarioNuevoController::class, 'iniciarInventario']);

Route::get('php/inventariofisico/estiba/documentos_iniciados.php', [InventarioNuevoController::class, 'documentosIniciados']);

Route::get('php/inventariofisico/estiba/get_inventario.php', [InventarioNuevoController::class, 'getInventario']);

Route::get('php/inventariofisico/estiba/consulta_producto.php', [InventarioNuevoController::class, 'consultaProducto']);

Route::post('php/inventariofisico/estiba/agrega_productos.php', [InventarioNuevoController::class, 'agregaProductos']);

Route::post('php/inventariofisico/estiba/gestion_de_estado.php', [InventarioNuevoController::class, 'gestionEstado']);

Route::post('php/inventariofisico/estiba/ajustar_inventario.php', [InventarioNuevoController::class, 'ajustarInventario']);

Route::get('php/inventariofisico/estiba/documentos_para_ajustar.php', [InventarioNuevoController::class, 'documentosAjustar']);

Route::get('php/inventariofisico/iniciar_inventario_barrido.php', [InventarioNuevoController::class, 'iniciarInventarioBarrido']);

Route::get('php/inventariofisico/consulta_producto_barrido.php', [InventarioNuevoController::class, 'consultaProductoBarrido']);

Route::post('php/inventariofisico/agrega_productos_barrido.php', [InventarioNuevoController::class, 'agregaProductosBarrido']);

Route::post('php/inventariofisico/estiba/guardar_inventario_final.php', [InventarioNuevoController::class, 'guardarInventarioFinal']);

Route::get('php/inventariofisico/estiba/validar_inventario.php', [InventarioNuevoController::class, 'validarInventario']);

Route::get('php/inventariofisico/inventario_sin_diferencia_barrido.php', [InventarioNuevoController::class, 'inventarioSinDiferenciaBarrido']);

Route::post('php/inventariofisico/estiba/guardar_reconteo.php', [InventarioNuevoController::class, 'guardarReconteo']);

Route::post('php/inventariofisico/estiba/descargar_excel_diferencias.php', [InventarioNuevoController::class, 'excelDiferencias']);

Route::get('php/inventariofisico/estiba/ver_inventario_terminado.php', [InventarioNuevoController::class, 'verInventarioTerminado']);

Route::post('php/inventariofisico/estiba/cambiar_estados_documentos.php', [InventarioNuevoController::class, 'cambiarEstadosDocumentos']);

Route::get('php/grupo_estiba/get_grupo_estibas.php', [InventarioNuevoController::class, 'getGrupoEstibas']);

Route::get('php/grupo_estiba/get_grupos_bodega.php', [InventarioNuevoController::class, 'getGrupoBodega']);

Route::get('php/inventariofisico/estiba/documentos_terminados.php', [InventarioNuevoController::class, 'documentosTerminados']);

Route::get('php/inventario_nuevo/filter_inventario.php', [InventarioNuevoController::class, 'filterInventario']);

Route::get('php/inventario_nuevo/ver_inventario_contrato.php', [InventarioNuevoController::class, 'verInventarioContrato']);

Route::get('php/inventario_nuevo/ver_apartadas.php', [InventarioNuevoController::class, 'verApartadas']);

Route::get('php/inventario_nuevo/ver_seleccionados.php', [InventarioNuevoController::class, 'verSeleccionados']);

Route::get('php/inventario/ver_compras.php', [InventarioNuevoController::class, 'verCompras']);

Route::get('php/archivos/descarga_etiqueta_controlado.php', [InventarioNuevoController::class, 'descargaEtiquetaControlado']);
