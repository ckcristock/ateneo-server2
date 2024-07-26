<?php

use App\Http\Controllers\ProductoDocInventarioAuditableController;
use Illuminate\Support\Facades\Route;

Route::get('php/inventario_auditor/show_inventario_Terminado.php', [ProductoDocInventarioAuditableController::class, 'showInventarioTerminado']);
