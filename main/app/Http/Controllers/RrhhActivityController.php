<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Person;
use App\Models\RrhhActivity;
use App\Models\RrhhActivityPerson;
use App\Models\RrhhActivityCycle;
use App\Services\PersonService;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RrhhActivityController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function index()
    {
        $year = date("Y");
        return
            $this->success(
                DB::table('rrhh_activities as a')
                    ->join('rrhh_activity_types as t', 't.id', '=', 'a.rrhh_activity_type_id')
                    ->leftJoin('dependencies as d', 'd.id', '=', 'a.dependency_id')
                    ->leftJoin('groups as g', 'g.id', '=', 'd.group_id')
                    ->select(
                        'a.*',
                        'a.id',
                        'a.state',
                        't.name as activity_type',
                        'a.hour_start',
                        'a.hour_end',
                        DB::raw('CAST(IFNULL(g.id, 0) AS SIGNED) as group_id'),
                        DB::raw('
                        concat( Date(a.date_end)," " ,a.hour_end) as start,
                        concat( Date(a.date_start)," " ,a.hour_start) as start
                        '),
                        DB::raw(' IF (a.dependency_id = "0","Todos",d.name) AS dependency_name'),
                        DB::raw(' IF (a.state = "Anulada","#FF5370",t.color) AS backgroundColor'),
                        DB::raw(' CONCAT(IF (a.state = "Anulada",
                                CONCAT(a.name," (ANULADA)" )
                                ,t.name)
                                , "-",  IF (a.dependency_id = "0","Todos",d.name) )
                                AS title'),
                    )
                    //->whereYear('a.date_start', $year)
                    ->where('a.state', '!=', 'Anulada')
                    ->where('a.company_id', $this->getCompany())
                    ->orderBy('a.date_start', 'DESC')
                    ->get()
            );
    }

    public function store(Request $request)
    {
        try {
            // Captura los datos del request
            $data = $request->all();

            // Asigna el ID de la compañía
            $data['company_id'] = $this->getCompany();
            $data['user_id'] = auth()->user()->id;

            // Convierte las fechas en Carbon para un mejor manejo
            $inicio = Carbon::parse($data['date_start']);
            $fin = Carbon::parse($data['date_end']);

            // Genera un código aleatorio
            $code = Str::random(25);
            // Actualiza una actividad existente
            if ($request->has('id')) {
                $this->updateActivity($request, $data);
            } else {
                // Itera a través de las fechas
                for ($i = $inicio; $i <= $fin; $i->addDay()) {
                    $data['date_start'] = $i->format('Y-m-d');
                    $data['date_end'] = $i->format('Y-m-d');

                    // Construye la descripción para usarlo en el alert
                    $date1 = $i->format('d M Y');
                    $date2 = $i->format('d M Y');
                    $description = 'Fecha: ' . $date1 . ' : ' . $data['hour_start'] . ' - '
                        . $date2 . ' : ' . $data['hour_end'] . ' Actividad: ' . $data['description'];

                    // Comprueba si es una fecha válida para la actividad
                    if (in_array($i->englishDayOfWeek, $data['days'])) {
                        // Genera un código si no se proporciona uno
                        if (!$request->has('id')) {
                            $data['code'] = $code;
                        }

                        // Crea la actividad
                        $activity = RrhhActivity::create($data);
                        if (isset($data['days']) && count($data['people_id']) > 0 || !$request->has('id')) {
                            if (!in_array('0', $data['people_id'])) {
                                // Asocia personas y crea alertas
                                foreach ($data['people_id'] as $person_id) {
                                    $this->createAlert($person_id, $data, $description, $activity->id);
                                    RrhhActivityPerson::create(['rrhh_activity_id' => $activity->id, 'person_id' => $person_id]);
                                }
                            } else {
                                // Si se selecciona "Todos", asocia a todas las personas disponibles
                                $this->associateAllPeople($data, $description, $activity);
                            }
                        }
                    }
                }
            }
            return $this->success('Guardado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . $th->getLine() . $th->getFile(), 500);
        }
    }

    private function createAlert($person_id, $data, $description, $activityId)
    {
        Alert::create([
            'person_id' => $person_id,
            'user_id' => $data['user_id'],
            'type' => 'Actividad',
            'icon' => 'fa fa-calendar-day',
            'title' => $data['name'],
            'description' => $description,
            'modal' => 1,
            'destination_id' => $activityId
        ]);
    }

    private function associateAllPeople($data, $description, $activity)
    {
        $people = PersonService::getPeopleE($data);
        foreach ($people as $person) {
            $this->createAlert($person->id, $data, $description, $activity->id);
            RrhhActivityPerson::create(['rrhh_activity_id' => $activity->id, 'person_id' => $person->id]);
        }
    }

    private function updateActivity($request, $data)
    {
        $activityId = $request->input('id');
        // primero eliminamos las personas
        RrhhActivityPerson::where('rrhh_activity_id', $activityId)->delete();

        $inicio = Carbon::parse($data['date_start']);
        $fin = Carbon::parse($data['date_end']);

        $data['date_start'] = $inicio->format('Y-m-d');
        $data['date_end'] = $fin->format('Y-m-d');

        // Construye la descripción para usarlo en el alert
        $date1 = $inicio->format('d M Y');
        $date2 = $fin->format('d M Y');
        $description = 'Fecha: ' . $date1 . ' : ' . $data['hour_start'] . ' - '
            . $date2 . ' : ' . $data['hour_end'] . ' Actividad: ' . $data['description'];

        $activity = RrhhActivity::find($activityId)->update($data);
        //dd($activity);
        if (count($data['people_id']) > 0) {
            if (!in_array('0', $data['people_id'])) {
                // Asocia personas y crea alertas
                foreach ($data['people_id'] as $person_id) {
                    $this->createAlert($person_id, $data, $description, $activityId);
                    RrhhActivityPerson::create(['rrhh_activity_id' => $activityId, 'person_id' => $person_id]);
                }
            } else {
                // Si se selecciona "Todos", asocia a todas las personas disponibles
                $this->associateAllPeople($data, $description, $activity);
            }
        }
    }

    public function getPeople($id)
    {
        return $this->success(
            RrhhActivityPerson::where('rrhh_activity_id', $id)
                ->with('person')
                ->get(['id', 'person_id'])
        );
    }

    public function cancel($id)
    {
        $activity = RrhhActivity::find($id);
        $activity->state = 'Anulada';
        $activity->save();
        return $this->success('Día anulado con éxito');
    }

    public function cancelCycle(Request $request, $code)
    {
        $activity = RrhhActivity::where('code', $code);
        $activity->update($request->all());
        return $this->success('Ciclo anulado con éxito');
    }
}
