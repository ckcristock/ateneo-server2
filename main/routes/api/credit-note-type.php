<?php

use App\Http\Controllers\CreditNoteTypeController;
use Illuminate\Support\Facades\Route;

Route::get('paginate-credit-note-types', [CreditNoteTypeController::class, 'paginate']);
Route::apiResource('credit-note-type', CreditNoteTypeController::class);
