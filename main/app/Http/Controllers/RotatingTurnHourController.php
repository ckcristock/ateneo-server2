<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\HistoryRotatingTurnHour;
use App\Models\Person;
use App\Models\RotatingTurnHour;
use App\Services\RotatingHourService;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;

class RotatingTurnHourController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }
    public function getDatosGenerales($semana)
    {
        $companies = Company::where("id", $this->getCompany())->get();
        foreach ($companies as $keyG => &$company) {
            $groups = DB::table("groups")
                ->when(Request()->get('group_id'), function ($q, $fill) {
                    $q->where('id', $fill);
                })->get(["id", "name"]);

            $groupsGlob = [];
            foreach ($groups as $key => &$group) {
                $dependencies = DB::table("dependencies")
                    ->when(Request()->get('dependency_id'), function ($q, $fill) {
                        $q->where('id', $fill);
                    })
                    ->where("group_id", $group->id)
                    ->get()->toArray();

                if (!$dependencies) {
                    unset($groups[$key]);
                    continue;
                }

                $depLocal = [];
                foreach ($dependencies as $key2 => &$dependency) {
                    $dependency->people = RotatingHourService::getPeople(
                        $dependency->id,
                        $company->id,
                    );
                    if (!$dependency->people) {
                        unset($dependencies[$key2]);
                        continue;
                    }
                    foreach ($dependency->people as &$person) {
                        $person->fixed_turn_hours = RotatingHourService::getHours(
                            $person->id,
                        );
                    }
                    $depLocal[] = $dependency;
                }
                if (!$depLocal) {
                    unset($groups[$key]);
                    continue;
                }
                $group->dependencies = [];
                $group->dependencies = $depLocal;
                $groupsGlob[] = $group;
            }
            if ($groups->isEmpty()) {
                unset($companies[$keyG]);
                continue;
            }
            $company->groups = $groupsGlob;
        }
        //return($companies);
        return $this->success($companies);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(RotatingTurnHour::all());
    }

    public function store()
    {
        /*  $atributos = request()->validate([
            'funcionario_id' => 'required',
            'turno_rotativo_id' => 'required',
            'fecha' => 'required',
            'numero_semana' => 'required',
        ]);
 */
        $data = Request()->all();
        try {
            $lastBatch = HistoryRotatingTurnHour::orderBy('id', 'desc')->value('batch');
            $lote = $lastBatch ? $lastBatch + 1 : 1;
            foreach ($data as $atributos) {
                $fecha = $atributos['date'];
                $personId = $atributos['person_id'];
                $action = null;
                $horarioExistente = RotatingTurnHour::where('date', $fecha)->where('person_id', $personId)->first();
                if ($horarioExistente) {
                    if ($horarioExistente->rotating_turn_id != $atributos['rotating_turn_id']) {
                        $action = 'edit';
                    }
                    $horarioExistente->update($atributos);
                    $id = $horarioExistente->id;
                } else {
                    $create = RotatingTurnHour::create($atributos);
                    $id = $create->id;
                    $action = 'create';
                }
                if ($action) {
                    HistoryRotatingTurnHour::create([
                        'rotating_turn_hour_id' => $id,
                        'person_id' => auth()->user()->person_id,
                        'batch' => $lote,
                        'action' => $action,
                        'company_id' => $this->getCompany()
                    ]);
                }
            }
            return $this->success('Horario asignado correctamente');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->success(RotatingTurnHour::find($id));
    }

    public function update($id)
    {
        dd($id);
    }
}
