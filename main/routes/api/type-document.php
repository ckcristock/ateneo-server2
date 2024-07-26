<?php

use App\Http\Controllers\TypeDocumentController;
use Illuminate\Support\Facades\Route;

Route::apiResource("type-documents", TypeDocumentController::class);
