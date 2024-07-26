<?php

use App\Http\Controllers\IngressTypesController;
use Illuminate\Support\Facades\Route;

Route::get('paginateIngressTypes', [IngressTypesController::class, 'paginate']);
Route::apiResource('ingress_types', IngressTypesController::class);

