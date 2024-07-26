<?php

use App\Http\Controllers\DataInit\EspecialidadController;
use App\Http\Controllers\DataInit\PersonController;
use App\Http\Controllers\DataInit\AgreementController;
use App\Http\Controllers\DataInit\AppointmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\TypeAppointmentController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Controllers\ClearCacheController;
use App\Http\Controllers\CupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

Auth::routes();
Route::view('{any}', 'home')->where('any', '.*');

Route::get('test', [TestController::class, 'test']);
Route::get('get-info', [TestController::class, 'getAppointmentByPatient']);

Route::get('clear-cache', [ClearCacheController::class, 'clearCache']);
Route::get('logs', [LogViewerController::class, 'index']);
Route::get('home', [HomeController::class, 'index'])->name('home');
Route::get('detalle-paciente/{identificacion}', [PacienteController::class, 'DetallePacienteOld']);
Route::get('detalle-paciente/{identificacion}/{tipo_documento}', [PersonController::class, 'customUpdateOld']);
Route::post('save-call', [PacienteController::class, 'store'])->middleware(EnsureTokenIsValid::class);
Route::get('get-tipos-agendamiento', [TypeAppointmentController::class, 'get']);
Route::get('create-tipos-agendamiento', [TypeAppointmentController::class, 'store']);
Route::get('get-appoinments', [AppointmentController::class, 'get']);
Route::get('create-appoinments', [AppointmentController::class, 'store']);
Route::get('get-persons', [PersonController::class, 'get']);
Route::get('create-persons', [PersonController::class, 'store']);
Route::get('create-especialidades', [EspecialidadController::class, 'store']);
Route::get('create-cups', [CupController::class, 'storeFromMedical']);
Route::get('create-cups', [CupController::class, 'storeFromMedical']);
Route::get('create-agreements', [AgreementController::class, 'store']);

// ->middleware(EnsureTokenIsValid::class);
// Route::get('insert-contracts', function ($id) {});
// Route::get('/get-regimes', [RegimenController::class, 'get']);
// Route::get('/create-regimes', [RegimenController::class, 'store']);
// Route::get('/get-ips', [IpsController::class, 'get']);
// Route::get('/create-ips', [IpsController::class, 'store']);
// Route::get('/get-especialidades', [EspecialidadController::class, 'get']);
// Route::get('/create-especialidades', [EspecialidadController::class, 'store']);
// Log::info('test');