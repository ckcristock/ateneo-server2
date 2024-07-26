<?php

use App\Models\History;
use App\Models\Person;
use Illuminate\Support\Facades\Validator;

if (!function_exists('addHistory')) {
    /**
     * @param array|\stdClass $data
     * @throws \InvalidArgumentException
     */
    function addHistory(\stdClass $data)
    {
        $companyId = Person::find(Auth()->user()->person_id)->company_worked_id;

        $rules = [
            //'person_id' => 'nullable|exists:people,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'icon' => 'nullable|string',
            'type' => 'required|string',
        ];
        $validator = Validator::make((array) $data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Datos no válidos: ' . $validator->errors()->first());
        }

        $history = new History([
            'person_id' => $data->person_id ?? 0,
            'user_id' => Auth()->id(),
            'company_id' => $companyId,
            'title' => $data->title,
            'description' => $data->description,
            'icon' => $data->icon ?? '',
            'type' => $data->type,
        ]);
        if (isset($data->historiable_type, $data->historiable_id)) {
            $model = app($data->historiable_type)->find($data->historiable_id);
            if ($model) {
                $model->histories()->save($history);
            }
        }
    }
}