<?php

use App\Http\Controllers\ApplicantController;
use Illuminate\Support\Facades\Route;

Route::apiResource('applicants', ApplicantController::class);
Route::get('download-applicants/{id}', [ApplicantController::class, 'donwloadCurriculum']);
