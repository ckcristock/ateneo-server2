<?php

use App\Http\Controllers\ThirdPartyController;
use Illuminate\Support\Facades\Route;

Route::resource('third-party', ThirdPartyController::class);
//Route::get('add-thirds-params', [ThirdPartyController::class, 'loanpdf']);
Route::get('third-parties-list', [ThirdPartyController::class, 'thirdParties']);
Route::put('activate-inactivate', [ThirdPartyController::class, 'changeState']);
Route::get('fields-third', [ThirdPartyController::class, 'getFields']);
Route::get('thirdPartyClient', [ThirdPartyController::class, 'thirdPartyClient']);
Route::get('third-party-provider', [ThirdPartyController::class, 'thirdPartyProvider']);
Route::get('php/terceros/filtrar_terceros.php', [ThirdPartyController::class, 'filtrarTerceros']);
Route::get('php/contabilidad/proveedor_buscar.php', [ThirdPartyController::class, 'buscarProveedor']);
Route::get('php/contabilidad/notascarteras/nit_buscar.php', [ThirdPartyController::class, 'nitBuscar']);
Route::get('php/clientes/get_terceros_por_tipo.php', [ThirdPartyController::class, 'porTipo']);
Route::get('php/comprobantes/lista_cliente.php', [ThirdPartyController::class, 'listaCliente']);
Route::get('php/comprobantes/lista_proveedores.php', [ThirdPartyController::class, 'listaProveedores']);
Route::get('php/productosavencer/listar_productos_vencer.php', [ThirdPartyController::class, 'listaProductosVencer']);