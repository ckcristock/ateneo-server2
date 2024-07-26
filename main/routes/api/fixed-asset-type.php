<?php

use App\Http\Controllers\FixedAssetTypeController;
use Illuminate\Support\Facades\Route;

Route::get('paginateFixedAssetType', [FixedAssetTypeController::class, 'paginate']);
Route::apiResource('fixed_asset_type', FixedAssetTypeController::class);
