<?php

use App\Http\Controllers\BonusPersonController;
use Illuminate\Support\Facades\Route;

Route::get('bonus-stub/{id}/{period}', [BonusPersonController::class, 'pdfGenerate']);
