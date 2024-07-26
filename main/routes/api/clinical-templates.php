<?php

use App\Http\Controllers\OptionController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\SpecialityTemplateController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateSectionController;
use App\Http\Controllers\VariableController;
use App\Http\Controllers\VariableTypeController;
use Illuminate\Support\Facades\Route;

Route::apiResource("templates", TemplateController::class);
Route::get("templates-paginate", [TemplateController::class, 'paginate']);
Route::apiResource("template-specialities", SpecialityTemplateController::class);
Route::apiResource("sections", SectionsController::class);
Route::get("sections-list", [SectionsController::class, 'listSections']);
Route::get("variable-type-list", [VariableTypeController::class, 'listarVariableTypes']);
Route::apiResource("template-sections", TemplateSectionController::class);
Route::apiResource("variable-types", VariableTypeController::class);
Route::apiResource("variables", VariableController::class);
Route::apiResource("options", OptionController::class);
