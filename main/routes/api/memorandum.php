<?php

use App\Http\Controllers\MemorandumController;
use Illuminate\Support\Facades\Route;

Route::get('memorandums-paginate', [MemorandumController::class, 'getMemorandum']);
Route::get('memorandums-download/{id}', [MemorandumController::class, 'download']);
Route::apiResource('memorandum', MemorandumController::class);
// Route::post('attentionCall', [MemorandumController::class, 'attentionCall']);
