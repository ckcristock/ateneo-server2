<?php

use App\Http\Controllers\AttentionCallController;
use Illuminate\Support\Facades\Route;

Route::apiResource('attention-call', AttentionCallController::class);
Route::get('attention-calls-validate/{id}', [AttentionCallController::class, 'callAlert']);
Route::get('attention-calls-paginate', [AttentionCallController::class, 'paginate']);
Route::get('attention-calls-download/{id}', [AttentionCallController::class, 'download']);

