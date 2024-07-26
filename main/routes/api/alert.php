<?php

use App\Http\Controllers\AlertController;
use Illuminate\Support\Facades\Route;

Route::apiResource('alerts', AlertController::class);

Route::controller(AlertController::class)->group(function () {
    Route::get('paginateAlert', 'paginate');
    Route::post('read-alert', 'read');
    Route::get('mark-all-notifications-as-read', 'markAllAsRead');
});
