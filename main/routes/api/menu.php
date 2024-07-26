<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::post('save-menu', [MenuController::class, 'store']);
Route::get('get-menu-by-person/{id}', [MenuController::class, 'getMenuByPerson']);

//? Comentada porque era una manera antigua de solicitar el menú del usuario, era demasiado lenta y hacía muchas peticiones a la base de datos
//Route::get('get-menu', [MenuController::class, 'getByPerson']);
