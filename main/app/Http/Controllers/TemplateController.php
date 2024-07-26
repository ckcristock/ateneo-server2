<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Section;
use App\Models\template;
use App\Http\Requests\StoretemplateRequest;
use App\Http\Requests\UpdatetemplateRequest;
use App\Models\ListModel;
use App\Models\Operator;
use App\Models\Option;
use App\Models\Rule;
use App\Models\TemplateSection;
use App\Models\Variable;
use App\Models\VariableConditionsValues;
use App\Models\VariableList;
use App\Models\VariableRule;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TemplateController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las plantillas con sus secciones y variables
        $templates = Template::with(['templateSections.section.variables'])->where('company_id', getCompanyWorkedId())->get();

        // Retornar la respuesta
        return $this->success($templates);
    }

    public function paginate(Request $request)
    {
        $page = $request->page ?? 1;
        $pageSize = $request->pageSize ?? 10;
        return $this->success(
            Template::orderBy('name')
                ->with(['templateSections.section.variables.typeConditions', 'specialities'])
                ->when($request->name, function ($q, $fill) {
                    $q->where('name', 'like', "%$fill%");
                })
                ->where('company_id', getCompanyWorkedId())
                ->paginate($pageSize, '*', 'page', $page)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoretemplateRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Crear la plantilla
            $template = Template::create([
                'name' => $validatedData['template']['name'],
                'company_id' => getCompanyWorkedId()
            ]);

            // Asociar las especialidades a la plantilla
            $template->specialities()->attach($validatedData['template']['specialities']);

            // Crear secciones y asociarlas con la plantilla
            $templateSections = [];
            foreach ($validatedData['secciones'] as $sectionData) {
                $section = Section::create([
                    'name' => $sectionData['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $templateSections[] = [
                    'template_id' => $template->id,
                    'section_id' => $section->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                // Creamos un array para almacenar las variables recién creadas
                $newVariables = [];
                // Crear variables y asociarlas con la sección
                foreach ($sectionData['variables'] as $variableData) {
                    $variable = Variable::create([
                        'section_id' => $section->id,
                        'variable_type_id' => $variableData['id_variable_type'],
                        'name' => $variableData['name'],
                        'size' => $variableData['size'] ?? null,
                        'required' => $variableData['required'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Almacenar la variable recién creada en el array
                    $newVariables[$variableData['name']] = $variable;

                    // Guardar los valores de condiciones en VariableConditionsValues
                    foreach ($variableData['conditions'] as $condition) {
                        VariableConditionsValues::create([
                            'variable_id' => $variable->id,
                            'type_condition_id' => $condition['id_condition'],
                            'value' => $condition['value'],
                        ]);
                    }

                    //guardar las opciones de la variable si es de tipo select nueva y no se va a guardar una lista
                    foreach ($variableData['options'] ?? [] as $option) {
                        Option::create([
                            'variable_id' => $variable->id,
                            'name' => $option['name'],
                        ]);
                    }
                    // dd($variableData);
                    if (isset($variableData['list']) && isset($variableData['list']['options'])) {
                        // Crear la lista y asociarla con la variable
                        $list = ListModel::create([
                            'name' => $variableData['list']['name'],
                        ]);
                        
                        // Guardar las opciones de la lista
                        foreach ($variableData['list']['options'] as $optionData) {
                            Option::create([
                                'list_id' => $list->id,
                                'name' => $optionData['name'],
                            ]);
                        }
    
                        // Asociar la variable con la lista
                        VariableList::create([
                            'variable_id' => $variable->id,
                            'list_id' => $list->id,
                        ]);
                    }


                    // Crear reglas y asociarlas con la variable
                    foreach ($variableData['rules'] ?? [] as $ruleData) {
                        // Crear una nueva variable para la regla
                        $ruleVariable = Variable::create([
                            'section_id' => $section->id,
                            'variable_type_id' => $ruleData['variable']['id_variable_type'],
                            'name' => $ruleData['variable']['name'],
                            'size' => $ruleData['variable']['size'] ?? null,
                            'required' => $ruleData['variable']['required'],
                            'parent' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        foreach ($ruleData['variable']['conditions'] as $conditionVar) {
                            VariableConditionsValues::create([
                                'variable_id' => $ruleVariable->id,
                                'type_condition_id' => $conditionVar['id_condition'],
                                'value' => $conditionVar['value'],
                            ]);
                        }

                        // Crear la regla y asociarla con la nueva variable
                        $rule = Rule::create([
                            'operator_id' => $ruleData['operator'],
                            'value' => $ruleData['value'],
                            'variable_id' => $ruleVariable->id,
                        ]);

                        // Crear la relación entre la variable y la regla
                        VariableRule::create([
                            'variable_id' => $variable->id,
                            'rule_id' => $rule->id,
                        ]);

                        // Crear condiciones de reglas y asociarlas con la regla
                        foreach ($ruleData['conditions'] as $ruleConditionData) {
                            // Buscamos la variable en el array de variables recién creadas
                            $variable = $newVariables[$ruleConditionData['variable']];
                            $rule->conditions()->create([
                                'operator_id' => $ruleConditionData['operator'],
                                'variable_id' => $variable->id,
                                'value' => $ruleConditionData['value'],
                                'logical_operator' => $ruleConditionData['logicalOperator'],
                            ]);
                        }
                    }
                }
            }

            // Insertar la relación entre plantilla y secciones
            TemplateSection::insert($templateSections);

            // Commit de la transacción
            DB::commit();

            // Retornar la respuesta
            return $this->success('Plantilla creada correctamente');
        } catch (Throwable $e) {
            // Si ocurre algún error, hacemos rollback de la transacción
            DB::rollBack();

            // Loguear el error
            logger()->error($e);

            // Retornar una respuesta de error
            return $this->error('Ocurrió un error al crear la plantilla. Por favor, inténtalo de nuevo más tarde.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(template $template)
    {
        // $template->load('specialities', 'templateSections.sections.variables.conditions', 'templateSections.sections.variables.rules', 'templateSections.sections.variables.rules.conditions');
        $template->load('specialities','templateSections.section.variables.lists.options','templateSections.section.variables.options', 'templateSections.section.variables.typeConditions', 'templateSections.section.variables.variableRules.rule.conditions', 'templateSections.section.variables.variableRules.rule.variable.typeConditions');
        return $this->success($template);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatetemplateRequest $request, template $template)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(template $template)
    {
        //
    }

}
