<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Deduction;
use App\Models\ExtraHourReport;
use App\Models\Lunch;
use App\Models\Person;
use App\Services\ExtraHoursService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExtraHoursController extends Controller
{
    use ApiResponser;

    protected $extrasService;

    public function __construct(ExtraHoursService $extrasService)
    {
        $this->extrasService = $extrasService;
    }

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function getDataRotative($fechaInicio, $fechaFin, $tipoTurno)
    {
        $dates = [$fechaInicio, $fechaFin];
        $companies = Company::where('id', $this->getCompany())->get();
        foreach ($companies as $keyG => &$company) {
            $groups = DB::table("groups")
                ->when(Request()->get('group_id'), function ($q, $fill) {
                    $q->where('id', $fill);
                })->get(["id", "name"]);

            $groupsGlob = [];
            foreach ($groups as $key => &$group) {
                $group->dependencies = DB::table('dependencies')
                    ->when(Request()->get('dependency_id'), function ($q, $fill) {
                        $q->where('id', $fill);
                    })
                    ->where('group_id', $group->id)->get();
                if ($group->dependencies->isEmpty()) {
                    unset($groups[$key]);
                    continue;
                }
                foreach ($group->dependencies as $key2 => &$dependency) {
                    $dependency->people = $this->extrasService->getPeople($dependency->id, $tipoTurno, $company->id, $dates);
                    if ($dependency->people->isEmpty()) {
                        unset($group->dependencies[$key2]);
                        continue;
                    }
                }
                if ($group->dependencies->isEmpty()) {
                    unset($groups[$key]);
                    continue;
                }
                $groupsGlob[] = $group;
            }
            if ($groups->isEmpty()) {
                unset($companies[$keyG]);
                continue;
            }
            $company->groups = $groupsGlob;
        }
        return $this->success($companies);
    }


    public function getInfoTotal()
    {
        $fechaInicio = request()->get('pd');
        $fechaFin = request()->get('ud');

        $filtroDiarioFecha = function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('date', [$fechaInicio, $fechaFin])->orderBy('date');
        };

        $tipo = strtolower(request()->get('tipo'));
        switch ($tipo) {
            case 'fijo':
                $funcionario = $this->extrasService->funcionarioFijo($filtroDiarioFecha, $fechaInicio, $fechaFin);
                return $this->success($this->extrasService->calcularExtras($funcionario));
            case 'rotativo':
                $funcionario = $this->extrasService->funcionarioRotativo($filtroDiarioFecha);
                return $this->success($this->extrasService->calcularExtras($funcionario));
            default:
                return abort(404);
        }
    }

    public function store()
    {
        try {
            $atributos = request()->validate([
                'person_id' => 'required',
                'date' => 'required',
                'ht' => 'required',
                'hed' => 'required',
                'hen' => 'required',
                //'heddf' => 'required',
                'hedfd' => 'required',
                //'hendf' => 'required',
                'hedfn' => 'required',
                //'rn' => 'required',
                'hrn' => 'required',
                //'rf' => 'required',
                'hrddf' => 'required',
                'hrndf' => 'required',
                'hed_reales' => 'required',
                'hen_reales' => 'required',
                'hedfd_reales' => 'required',
                'hedfn_reales' => 'required',
                'rn_reales' => 'required',
                'rf_reales' => 'required',
                'rnf_reales' => 'required'
            ]);
            $nuevo = ExtraHourReport::create($atributos);

            //resisar este algoritmo
            $hed_reales = request()->get('hed_reales');
            $hen_reales = request()->get('hen_reales');
            $hedfd_reales = request()->get('hedfd_reales');
            $hedfn_reales = request()->get('hedfn_reales');
            $sum = ($hed_reales + $hen_reales + $hedfd_reales + $hedfn_reales);
            $lunch = Lunch::where('person_id', request()->get('person_id'))->first();
            if ($sum >= 3.5) {
                if (isset($lunch)) {
                    $lunch->update(['apply' => ('Si')]);
                }
            } else {
                if (isset($lunch)) {
                    Deduction::create([
                        'person_id' => request()->get('person_id'),
                        'countable_deduction_id' => 5,
                        'value' => $lunch->value
                    ]);
                    $lunch->update(['apply' => ('No')]);
                }
            }
            if ($nuevo) {
                return $this->success(['message' => 'Horas extras validadas correctamente']);
            } else {
                return $this->error('Hubo un error, no se pudo guardar', 201);
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 201);
        }
    }

    public function storeWeek(Request $request)
    {
        try {
            foreach ($request->all() as $valor) {
                if (gettype(strtotime($valor['date'])) == 'integer') {
                    if (!$valor['validada']) {
                        $validator = Validator::make($valor, [
                            'person_id' => 'required',
                            'date' => 'required',
                            'ht' => 'required',
                            'hed' => 'required',
                            'hen' => 'required',
                            'heddf' => 'required',
                            'hendf' => 'required',
                            'hrn' => 'required',
                            'hrndf' => 'required',
                            'hrddf' => 'required',
                            'hed_reales' => 'required',
                            'hen_reales' => 'required',
                            'hedfd_reales' => 'required',
                            'hedfn_reales' => 'required',
                            'rn_reales' => 'required',
                            'rf_reales' => 'required',
                            'rnf_reales' => 'required'
                        ], [
                            'required' => 'El campo :attribute es requerido.'
                        ]);

                        if ($validator->fails()) {
                            return $this->error(['person_id' => $valor['person_id'], 'errores' => $validator->errors()], 204);
                        }
                        $nuevo = ExtraHourReport::updateOrCreate($validator->validated());
                        $hed_reales = $request->hed_reales;
                        $hen_reales = $request->hen_reales;
                        $hedfd_reales = $request->hedfd_reales;
                        $hedfn_reales = $request->hedfn_reales;
                        $sum = ($hed_reales + $hen_reales + $hedfd_reales + $hedfn_reales);
                        $lunch = Lunch::where('person_id', request()->get('person_id'))->first();
                        if ($sum >= 3.5) {
                            if (isset($lunch)) {
                                $lunch->update(['apply' => ('Si')]);
                            }
                        } else {
                            if (isset($lunch)) {
                                Deduction::create([
                                    'person_id' => request()->get('person_id'),
                                    'countable_deduction_id' => 5,
                                    'value' => $lunch->value
                                ]);
                                $lunch->update(['apply' => ('No')]);
                            }
                        }
                    }
                } else {
                    return $this->error('La fecha a guardar no es válida', 205);
                }
            }
            return $this->success('Semana de horas extras guardada exitosamente');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 204);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $validada = ExtraHourReport::findOrFail($id);
            $atributos = request()->validate([
                'person_id' => 'required',
                'date' => 'required',
                'ht' => 'required',
                'hed' => 'required',
                'hen' => 'required',
                'hedfd' => 'required',
                'hedfn' => 'required',
                'rn' => 'required',
                'rf' => 'required',
                'hed_reales' => 'required',
                'hen_reales' => 'required',
                'hedfd_reales' => 'required',
                'hedfn_reales' => 'required',
                'rn_reales' => 'required',
                'rf_reales' => 'required',
            ]);
            $validada->update($atributos);
            $hed = request()->get('hed');
            $hen = request()->get('hen');
            $hedfd = request()->get('hedfd');
            $hedfn = request()->get('hedfn');
            $sum = ($hed + $hen + $hedfd + $hedfn);
            $lunch = Lunch::where('person_id', request()->get('person_id'))->where('state', 'Activo')->first();
            if ($sum >= 3.5) {
                if (isset($lunch)) {
                    Deduction::create([
                        'person_id' => request()->get('person_id'),
                        'countable_deduction_id' => 5,
                        'value' => $lunch->value
                    ]);
                    $lunch->update(['apply' => ('Si')]);
                }
            } else {
                if (isset($lunch)) {
                    $lunch->update(['apply' => ('No')]);
                }
            }
            return $this->success(['message' => 'Horas extras validadas correctamente']);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 204);
        }
    }

    public function getDataValid($person_id, $fecha)
    {
        $validada = ExtraHourReport::where('date', $fecha)->where('person_id', $person_id)->orderBy('date')->first();
        if ($validada) {
            return $this->success($validada);
        } else {
            return $this->error('Entrada no encontrada', 204);
        }
    }
}
