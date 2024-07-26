<?php

use App\Http\Controllers\DataInit\PersonController as DataInitPersonController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::apiResource('people', PersonController::class);
Route::apiResource("person", PersonController::class);
Route::get('afiliation/{id}', [PersonController::class, 'afiliation']);
Route::get('basicData/{id}', [PersonController::class, 'basicDataForm']);
Route::get('fixed_turn', [PersonController::class, 'fixedTurn']);
Route::get('get-file-permission/{id}', [PersonController::class, 'getFilePermission']);
Route::get('my-profile', [PersonController::class, 'myProfle']);
Route::get('peopleSelects', [PersonController::class, 'peopleSelects']);
Route::get('person-company', [PersonController::class, 'getPersonCompany']);
Route::get('person-profile/{id}', [PersonController::class, 'getProfile']);
Route::get('person/{id}', [PersonController::class, 'basicData']);
Route::get('person/get-boards/{personId}', [PersonController::class, 'personBoards']);
Route::get('person/get-companies/{personId}', [PersonController::class, 'personCompanies']);
Route::get('person/train', [PersonController::class, 'train']);
Route::get('php/inventario_fisico_puntos/lista_punto_funcionario.php', [PersonController::class, 'funcionarioPunto']);
Route::get('php/inventario_fisico_puntos/lista_punto_funcionario', [PersonController::class, 'funcionarioPunto']);
Route::get('salary-history/{id}', [PersonController::class, 'salaryHistory']);
Route::get('salary/{id}', [PersonController::class, 'salary']);
Route::get('users/{id}', [PersonController::class, 'user']);
Route::get("company-people-all", [PersonController::class, "getAllCompany"]);
Route::get("download-people", [PersonController::class, 'download']);
Route::get("get-professionals/{ips?}/{speciality?}", [PersonController::class, 'index']);
Route::get("people-all", [PersonController::class, "getAll"]);
Route::get("people-paginate", [PersonController::class, 'indexPaginate']);
Route::get("people-with-dni", [PersonController::class, "peoplesWithDni"]);
Route::get("person-view/{id}", [PersonController::class, 'personView']);
Route::get("validar-cedula/{documento}", [PersonController::class, "validarCedula"]);
Route::get("validate-info-patient", [DataInitPersonController::class, "validatePatientByLineFront"]);
Route::post('change-company-work/{id}', [PersonController::class, 'changeCompanyWorked']);
Route::post('change-point', [PersonController::class, 'changePoint']);
Route::post('person/set-board/{personId}/{board}', [PersonController::class, 'setBoardsPerson']);
Route::post('person/set-companies/{personId}', [PersonController::class, 'setCompaniesWork']);
Route::post('salary', [PersonController::class, 'updateSalaryInfo']);
Route::post('update-file-permission', [PersonController::class, 'updateFilePermission']);
Route::post('updateAfiliation/{id}', [PersonController::class, 'updateAfiliation']);
Route::post('updatebasicData/{id}', [PersonController::class, 'updateBasicData']);
Route::put('blockOrActivate/{id}', [PersonController::class, 'blockOrActivateUser']);
Route::put('liquidate/{id}', [PersonController::class, 'liquidate']);
Route::put('liquidateOrActivate/{id}', [PersonController::class, 'liquidateOrActivate']);

//? Person Configuration Routes
Route::get('person-configuration/{person_id}', [PersonController::class, 'personConfiguration']);
Route::put('person-configuration/{person_id}', [PersonController::class, 'updatePersonConfiguration']);
//Route::get('epss-person', [PersonController::class, 'epss']);
Route::get('php/tablero/detalle_perfil.php', [PersonController::class, 'detallePerfil']);
Route::get('php/inventariopuntos/lista_punto_funcionario.php', [PersonController::class, 'listaPuntoFuncionario']);
