<?php

use App\Models\Company;
use App\Models\HistoryDataCompany;
use Carbon\Carbon;

if (!function_exists('findDifference')) {
    function findDifference($data, $model)
    {
        //! aqui vas a contruir una coleccion
        $differences = [];
        // obtener los datos originales
        $id = isset($data['id']) ? $data['id'] : $data['company_id'];
        $campo = isset($data['id']) ? 'id' : 'company_id';
        $old_data = $model::where($campo, $id)->first();
        //dd($data);
        //comparar cada campo de los datos originales con los datos recibidos en $data
        foreach ($data as $key => $value) {
            if ($old_data->$key != $value) {
                //guardar el valor antiguo en differences
                $differences[$key] = $old_data->$key;
            }
        }
        //devolver differences
        return $differences;
    }
}

if (!function_exists('saveHistoryCompanyData')) {
    function saveHistoryCompanyData($differences, $model, $company_id)
    {
        foreach ($differences as $key => $value) {
            HistoryDataCompany::create([
                'namespace' => $model,
                'data_name' => $key,
                'date_end' => Carbon::now(),
                'value' => $value,
                'person_id' => auth()->user()->person_id,
                'company_id' => $company_id
            ]);
        }
    }
}
