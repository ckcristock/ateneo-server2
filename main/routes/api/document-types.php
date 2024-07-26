<?php

use App\Http\Controllers\DocumentTypesController;
use Illuminate\Support\Facades\Route;

Route::get('paginateDocumentType', [DocumentTypesController::class, 'paginate']);
Route::apiResource("type-documents", DocumentTypesController::class);
Route::apiResource('documentTypes', DocumentTypesController::class);
