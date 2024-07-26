<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get("task", [TaskController::class, "getData"]);
Route::post('upload', [TaskController::class, 'upload']);
Route::get('deletetask/{idTask}', [TaskController::class, 'deleteTask']);
Route::get('adjuntostask/{idTask}', [TaskController::class, 'adjuntosTask']);
Route::get('taskview/{id}', [TaskController::class, 'taskView']);
Route::post('newtask/{task}', [TaskController::class, 'newTask']);
Route::post('newcomment/{comment}', [TaskController::class, 'newComment']);
Route::get('deletecomment/{commentId}', [TaskController::class, 'deleteComment']);
Route::get('getarchivada/{id}', [TaskController::class, 'getArchivada']);
Route::get('task/{id}', [TaskController::class, 'personTask']);
Route::get('getcomments/{idTask}', [TaskController::class, 'getComments']);
Route::get('taskperson/{personId}', [TaskController::class, 'person']);
Route::get('taskfor/{id}', [TaskController::class, 'personTaskFor']);
Route::get('person-taskpendientes/{personId}', [TaskController::class, 'personTaskPendientes']);
Route::get('person-taskejecucion/{personId}', [TaskController::class, 'personTaskEjecucion']);
Route::get('person-taskespera/{personId}', [TaskController::class, 'personTaskEspera']);
Route::get('person-taskfinalizado/{personId}', [TaskController::class, 'personTaskFinalizado']);
Route::post('updatefinalizado/{id}', [TaskController::class, 'updateFinalizado']);
Route::post('updatependiente/{id}', [TaskController::class, 'updatePendiente']);
Route::post('updateejecucion/{id}', [TaskController::class, 'updateEjecucion']);
Route::post('updateespera/{id}', [TaskController::class, 'updateEspera']);
Route::post('updatearchivada/{id}', [TaskController::class, 'updateArchivado']);
