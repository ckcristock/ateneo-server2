<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ThirdParty;
use App\Models\ThirdPartyField;
use App\Traits\ApiResponser;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThirdPartyFieldController extends Controller
{
    use ApiResponser;
    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->success(
            ThirdPartyField::when($request->name, function ($q, $fill) {
                $q->where('name', 'like', '%' . $fill . '%');
            })
            ->where('company_id', $this->getCompany())
            ->paginate(request()->get('pageSize', 5), ['*'], 'page', request()->get('page', 1))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */

    function buildFieldName($field_name)
    {
        $palabras_campo = explode(" ", $field_name);
        $nueva_palabra = '';
        foreach ($palabras_campo as $palabra) {
            $p = strtolower($palabra);
            $nueva_palabra .= $p . '_';
        }
        return trim($nueva_palabra, "_");
    }

    public function store(Request $request)
    {
        try {
            $field = ThirdPartyField::create([
                'label' => $request->name,
                'name' => $this->buildFieldName($request->name),
                'type' => $request->type,
                'required' => $request->required,
                'length' => $request->length,
                'company_id' => $this->getCompany(),
            ]);
            if (isset($field)) {
                $type = '';
                if ($field->type == 'text') {
                    $type = ' VARCHAR(200)';
                } elseif ($field->type == 'number') {
                    if ($field->length > 10) {
                        $type = ' BIGINT(20)';
                    } else {
                        $type = ' INT(20)';
                    }
                } elseif ($field->type == 'date') {
                    $type = ' DATE';
                }
                $sql = DB::unprepared('ALTER TABLE `third_parties` ADD COLUMN' . ' '  . $this->buildFieldName($field->name) . $type . ' NULL DEFAULT NULL');
            }
            return ($field->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            $field->delete();
            return $this->error($th->getMessage(), 500);
        }
    }

    public function changeState(Request $request, $id)
    {
        try {
            $field = ThirdPartyField::find($id);
            $field->update($request->all());
            return $this->success('Estado cambiado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
