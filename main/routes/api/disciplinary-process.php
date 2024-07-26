<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DisciplinaryProcessController;

// !sugerencia Route::get('processByPerson/{id}', [DisciplinaryProcessController::class, 'process']);

//Route::get('disciplinary_process-history/{id}', [DisciplinaryProcessController::class, 'history']);

Route::apiResource('disciplinary_process', DisciplinaryProcessController::class);

Route::get('process/{id}', [DisciplinaryProcessController::class, 'process']);

Route::post('disciplinary_process-closure', [DisciplinaryProcessController::class, 'close']);

Route::get('memorandums-person/{id}', [DisciplinaryProcessController::class, 'getMemorandumsForPeople']);

Route::post('disciplinary-process-actions', [DisciplinaryProcessController::class, 'saveActions']);

Route::put('process/{processId}', [DisciplinaryProcessController::class, 'update']);

Route::get('descargo/{id}', [DisciplinaryProcessController::class, 'descargoPdf']);

Route::get('legal_document/{disciplinary_process_id}', [DisciplinaryProcessController::class, 'legalDocument']);

Route::post('disciplinary-notice-download/{id}', [DisciplinaryProcessController::class, 'download']);

Route::post('legal_document', [DisciplinaryProcessController::class, 'saveLegalDocument']);

Route::put('legal_document/{id}', [DisciplinaryProcessController::class, 'InactiveDOcument']);

Route::post('approve_process/{disciplinary_process_id}', [DisciplinaryProcessController::class, 'approve']);
