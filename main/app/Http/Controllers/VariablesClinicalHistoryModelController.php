<?php

namespace App\Http\Controllers;

use App\variablesHistoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariablesClinicalHistoryModelController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            foreach (request()->get('fields') as $field) {
                $variable = DB::table('variables_clinical_history_models')->insertGetId([
                    "module_clinical_history_model_id" => request()->get("id"),
                    "property" => $field["property"],
                    "name" => $field["property"],
                    "type" => $field["type"],
                    "dependence" => $field["dependencia"],
                    "required" => $field["required"],
                    "valor" => (($field["type"] != 'select' && $field["type"] != 'check') && ($field['value'])) ? $field['value'] : ''

                ]);

                // ***********************************************************************************parents_for_fields********************************************************************
                if ($field['parent']) {
                    $parents = explode(',', $field['parent']);
                    foreach ($parents as $parent) {

                        $parentModel = DB::table('variables_clinical_history_models')->select('id')->where('name', $parent)->where('module_clinical_history_model_id', request()->get("id"))->first();
                        $parents_for_fields = DB::table('parents_for_fields')->insertGetId([
                            'parent_id' => $parentModel->id,
                            'variables_clinical_history_model_id' => $variable,
                        ]);

                        // ***********************************************************************fields_dependences********************************************************************
                        if ($field['valueDependend']) {
                            $dependences = explode(',', $field['valueDependend']);
                            foreach ($dependences as $dependence) {

                                DB::table('fields_dependences')->insertGetId([
                                    'parents_for_fields_id' => $parents_for_fields,
                                    'name' => $dependence,
                                ]);
                            }
                        }


                    }
                }



                // ***********************************************************************values_for_selects********************************************************************
                if ($field["type"] == 'select' || $field["type"] == 'check') {

                    if ($field['value']) {
                        $values = explode(',', $field['value']);
                        foreach ($values as $value) {

                            DB::table('values_for_selects')->insertGetId([
                                'variables_clinical_history_model_id' => $variable,
                                'name' => $value,
                            ]);
                        }
                    }

                }
            }


            return response()->success('Operación realizada correctamente');

        } catch (\Exception $th) {
            return response()->error([$th->getMessage(), $th->getLine()]);
        }


    }
}
