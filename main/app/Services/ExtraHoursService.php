<?php

namespace App\Services;

use App\Clases\stdObject;
use App\Models\Person;
use App\Models\RotatingTurn;
use App\Services\TranslateService;
use App\Traits\CalculateRotativoExtras;
use App\Traits\CalculateRotativoRecargos;
use App\Traits\Festivos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtraHoursService
{
    use Festivos;
    use CalculateRotativoExtras;
    use CalculateRotativoRecargos;

    public $tiempoParaExtras;

    /**Funciones de estadisticas */
    public function getLates($dates)
    {
        // ? Al parecer esto no se usa
        return DB::table('late_arrivals as l')
            ->join('people as p', 'l.person_id', '=', 'p.id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->whereBetween(DB::raw('DATE(l.created_at)'), $dates)
            ->when(Request()->get('company_id'), function ($q, $fill) {
                $q->where('w.company_id', $fill);
            })
            ->selectRaw('count(*) as total')
            ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(l.real_entry) - TIME_TO_SEC(l.entry))) AS time_diff_total')
            ->first();
    }

    public function getAllByDependecies($dates)
    {
        // ? Al parecer esto no se usa
        return DB::table('late_arrivals as l')
            ->join('people as p', 'l.person_id', '=', 'p.id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->join('positions as ps', 'ps.id', '=', 'w.position_id')
            ->join('dependencies as d', 'd.id', '=', 'ps.dependency_id')
            ->when(Request()->get('company_id'), function ($q, $fill) {
                $q->where('w.company_id', $fill);
            })
            ->whereBetween(DB::raw('DATE(l.created_at)'), $dates)
            ->selectRaw('count(*) as total, d.name')
            ->groupBy('d.id')
            ->get();
    }

    /**Arrivals */

    public function getPeople($id, $turn, $company_id, $dates)
    {
        // ? Recibe el company_id ya filtrado
        $query = DB::table('people as p')
            ->join('work_contracts as w', function ($join) use ($dates) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->join('positions as ps', 'ps.id', '=', 'w.position_id')
            ->where('ps.dependency_id', $id)
            ->where('w.company_id', $company_id)
            ->where('w.turn_type', $turn)
            ->where('p.status', '!=', 'liquidado')
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('p.id', $fill);
            });
        if ($turn == 'rotativo') {
            $query->whereExists(function ($query) use ($dates) {
                $query->select(DB::raw(1))
                    ->from('rotating_turn_hours as la')
                    ->whereColumn('la.person_id', 'p.id')
                    ->whereBetween(DB::raw('DATE(la.date)'), $dates);
            });
        }

        return $query->select('p.first_name', 'p.first_surname', 'p.id', 'p.image', 'w.turn_type')
            ->orderBy("p.first_name")
            ->get();
    }

    public function calcularExtras($funcionario)
    {
        //Elimino la cosulta para evitar incrementar el tiempo de respuesta
        //Esto es el máximo de la empresa semanal
        //$tiempoAsignado = Company::first()->toArray()['work_time'];
        //$tiempoAsignado = $funcionario['company_worked']['work_time'];

        $listaExtras = array();

        /**
         * Evaluar si el horario permite horas extras
         * sino, no calcular
         * Se toma en cuenta el horario para saber si es hora extra o recargo
         * aunque llegue temprano, no se toma como extra hasta que cumpla
         * las horas estipuladas para el turno
         */
        //dd($funcionario);
        foreach ($funcionario['days_work'] as $day) {
            if (isset($day)) {
                $turno = Request()->get('tipo');
                $diario = isset($day[0]) ? $day[0]['day'] : null;
                if (!is_null($diario)) {
                    $dayStart = Carbon::create($diario['date'] . ' ' . $funcionario['company_worked']['night_end_time']);
                    $dayEnd = Carbon::create($diario['date'] . ' ' . $funcionario['company_worked']['night_start_time']);
                    if ($turno == 'Fijo') {
                        $horasExtras = $funcionario['turno_fijo']['extra_hours'];
                        $toleranciaSalida = $funcionario['turno_fijo']['leave_tolerance'];
                        $toleranciaEntrada = $funcionario['turno_fijo']['entry_tolerance'];
                    } elseif ($turno == 'Rotativo') {
                        $horasExtras = $diario['turnoOficial']['extra_hours'];
                        $toleranciaSalida = $diario['turnoOficial']['entry_tolerance'];
                        $toleranciaEntrada = $diario['turnoOficial']['leave_tolerance'];
                    }
                } else { //sino hay diario, no se registró ingreso a la empresa
                    $horasExtras = false;
                    if (isset($day['date'])) {
                        if (is_string($day['date'])) {
                            $semDia = Carbon::create($day['date']);
                        } else {
                            $semDia = $day['date'];
                        }
                    }
                    $translateService = new TranslateService();
                    $diferencias = new stdObject([
                        'date' => $semDia ?? $day['day'],
                        'day' => isset($semDia) ? $translateService->traslateIntDay($semDia->dayOfWeek) : $day['day'],
                        'extras' => 'No hay asistencia este día',
                    ]);
                    array_push($listaExtras, $diferencias);
                }
                /**
                 * sino es fijo, es rotativo, entonces le asigno la tolerancia del rotativo
                 * (06-12-22) aún no evalúo el rotativo, pendiente
                 */

                if ($turno == 'Fijo' && !is_null($diario) && $horasExtras == true) {
                    $diferenciasDia = $this->diferenciaHorariosFijo($day, $toleranciaEntrada, $toleranciaSalida, $dayStart, $dayEnd);
                    array_push($listaExtras, $diferenciasDia);
                    //calculo horas extras
                } elseif ($turno == 'Rotativo' && !is_null($diario)) {
                    $diferenciasDia = $this->diferenciaHorariosRotativo($diario, $toleranciaEntrada, $toleranciaSalida, $dayStart, $dayEnd);
                    array_push($listaExtras, $diferenciasDia);
                    //Este turno no posee Horas Extras
                }
                //OJO, debe retornar $funcionario
            } else {
                // No trabajó
                $funcionario['extras'] = $listaExtras;
                return $funcionario;
            }
        }
        $funcionario['extras'] = $listaExtras;
        return $funcionario;
    }

    /**
     * función para calcular la direfencia entre los horarios y las horas reales
     * recibe el horario en arreglo por día y el diario de horas reales,
     * devuelve un arreglo por día con su Hora Extra u Hora Recargo
     * //primero se está considerando solo los fijos
     */
    public function diferenciaHorariosFijo($horario, $toleranciaEntrada, $toleranciaSalida, $comienzoDia, $finDia)
    {
        $diario = $horario[0]['day'];

        //horario mañana
        $esperadoIn = Carbon::create($diario['date'] . $horario['entry_time_one']);
        $realIn = Carbon::create($diario['date'] . $diario['entry_time_one']);
        $esperadoOut = Carbon::create($diario['date'] . $horario['leave_time_one']);
        $realOut = Carbon::create($diario['date'] . $diario['leave_time_one']);
        $cantHorasHorario = new stdObject([
            'horas' => intval($esperadoIn->diff($esperadoOut)->format('%H')),
            'minutos' => intval($esperadoIn->diff($esperadoOut)->format('%i')),
            'segundos' => intval($esperadoIn->diff($esperadoOut)->format('%s'))
        ]);
        $cantHorasDiario = new stdObject([  //horas trabajadas
            'horas' => intval($realIn->diff($realOut)->format('%H')),
            'minutos' => intval($realIn->diff($realOut)->format('%i')),
            'segundos' => intval($realIn->diff($realOut)->format('%s'))
        ]);
        ////horario tarde
        $esperadoInDos = Carbon::create($diario['date'] . $horario['entry_time_two']);
        $realInDos = Carbon::create($diario['date'] . $diario['entry_time_two']);
        $esperadoOutDos = Carbon::create($diario['date'] . $horario['leave_time_two']);
        $realOutDos = Carbon::create($diario['date'] . $diario['leave_time_two']);
        $cantHorasHorario->horas += intval($esperadoInDos->diff($esperadoOutDos)->format('%H'));
        $cantHorasHorario->minutos += intval($esperadoInDos->diff($esperadoOutDos)->format('%i'));
        $cantHorasHorario->segundos += intval($esperadoInDos->diff($esperadoOutDos)->format('%s'));
        $cantHorasDiario->horas += intval($realInDos->diff($realOutDos)->format('%H'));
        $cantHorasDiario->minutos += intval($realInDos->diff($realOutDos)->format('%i'));
        $cantHorasDiario->segundos += intval($realInDos->diff($realOutDos)->format('%s'));
        $diferenciaReal = new stdObject([
            'horas' => $cantHorasDiario->horas - $cantHorasHorario->horas,
            'minutos' => $cantHorasDiario->minutos - $cantHorasHorario->minutos,
            'segundos' => $cantHorasDiario->segundos - $cantHorasHorario->segundos
        ]);
        $intSalida = $this->redondearHora($diferenciaReal, $toleranciaSalida);
        $cantDiarios = $this->redondearHora($cantHorasDiario, $toleranciaSalida);
        $horasExtras = $this->clasificacionHorasExtras($intSalida, $cantDiarios, $diario, $realOutDos, $comienzoDia, $finDia);
        $horasRecargo = new stdObject([
            "hrndf" => 0,
            "hrn" => 0,
            "hrddf" => 0
        ]);
        if ($realOut > $finDia) {
            $recargo = ($esperadoOut->diffInHours($finDia));
            $horasRecargo = $this->clasificacionHorasRecargo($recargo, $horario, $realOut, $comienzoDia, $finDia);
        }

        $diferencias = new stdObject([
            'date' => $diario['date'],
            'day' => $horario['day'],
            'extras' => $intSalida,
            'hour_in' => $realIn->toDateTimeString(),
            'hour_out' => $realOutDos->toDateTimeString(),
            'hours_extra' => $horasExtras,
            'hours_recharge' => $horasRecargo,
            'hours_schedule' => $cantHorasHorario,
            'schedule_in' => $esperadoIn->toDateTimeString(),
            'schedule_out' => $esperadoOutDos->toDateTimeString()
        ]);

        return $diferencias;
    }

    /**
     * función para calcular de acuerdo a la tolerancia si es ua hora o no
     * @param $diferencia // tipo: array['horas'= int, 'minutos'=int, 'segundos'=int]
     * Dy
     */
    public function redondearHora($diferencia, $tolerancia)
    {
        //la tolerancia viene en minutos
        //$horaExtra = 0;
        //dd($diferencia->minutos + $tolerancia);

        $horaExtra = $diferencia->horas;
        if ($tolerancia <> 0) {
            if ($diferencia->minutos > $tolerancia) {
                $horaExtra++;
            }
        }
        return $horaExtra;
    }

    /**
     * función para clasificar el tipo de hora
     * recibe la fecha, hora inicio jornada diurna, hora fin jornada diurna
     * devuelve un arreglo  boolean con la clasificación de hora [siEsDomingo, siEsFeriado, siEsDiurno ]
     * la fecha debe ser del mismo día
     * @param $fecha //ejemplo "2022-12-11"
     * @param $hora  //ejemplo Carbon::create("2022-12-11 05:01:43")
     * @param $comienzoDia  //ejemplo "2022-12-11 06:01:00"
     * @param $finDia  //ejemplo  "2022-12-11 21:01:00"
     * Dy (07-12-2022 9:40am)
     */
    public function tipoHora($fecha, $hora, $comienzoDia, $finDia)
    {
        $fecha = Carbon::create($fecha);

        $domingo = $fecha->dayOfWeek == 0 ? true : false;

        $festivosAnio = $this->getFestivos();
        $festivo = false;
        if (!$domingo) {
            foreach ($festivosAnio as $festive) {
                $festCarbon = Carbon::create($festive);
                if ($fecha == $festCarbon) {
                    $festivo = true;
                    $domingo = true;
                }
            }
        }
        //formato de festivos "YYY-mm-dd"

        //Hora se recibe en formato Carbon::create("2022-12-11 05:01:43")
        //por ello no se convierte acá
        $diurno = false;
        if ($hora->betweenIncluded($comienzoDia, $finDia)) {
            $diurno = true;
        }

        return [$domingo, $diurno];
    }

    /**
     * función para clasificar cada una de las horas extras, recibe:
     * @param
     */
    public function clasificacionHorasExtras($intSalida, $cantHorasDiario, $diario, $salida, $comienzoDia, $finDia)
    {
        $horasExtras = new stdObject([
            "ht" => $cantHorasDiario,
            "hed" => 0,
            "hen" => 0,
            "heddf" => 0,
            "hendf" => 0,
        ]);
        $aux = $salida;
        for ($i = 1; $i <= $intSalida; $i++) {
            $array = $this->tipoHora($diario['date'], $aux->subHours($i - 1), $comienzoDia, $finDia);

            if (($array <=> [false, true]) == 0) {    //H E D
                $horasExtras->hed++;
            } elseif (($array <=> [false, false]) == 0) {  //H E N
                $horasExtras->hen++;
            } elseif (($array <=> [true, true]) == 0) {  //H E D DF
                $horasExtras->heddf++;
            } elseif (($array <=> [true, false]) == 0) {  //H E N DF
                $horasExtras->hendf++;
            }
            $salida->addHours($i - 1);
        }
        return $horasExtras;
    }

    public function clasificacionHorasRecargo($intSalida, $diario, $salida, $comienzoDia, $finDia)
    {
        $horasRecargo = new stdObject([
            "hrndf" => 0,
            "hrn" => 0,
            "hrddf" => 0
        ]);

        $aux = $salida;
        for ($i = 1; $i <= $intSalida; $i++) {
            $array = $this->tipoHora($diario['date'], $aux->subHours($i - 1), $comienzoDia, $finDia);

            if (($array <=> [true, false]) == 0) {  //H R N DF
                $horasRecargo->hrndf++;
            } elseif (($array <=> [false, false]) == 0) {  //H R N
                $horasRecargo->hrn++;
            } elseif (($array <=> [true, true]) == 0) {  //H R D DF
                $horasRecargo->hrddf++;
            }
            $salida->addHours($i - 1);
        }
        return $horasRecargo;
    }

    public function diferenciaHorariosRotativo($horario, $toleranciaEntrada, $toleranciaSalida, $comienzoDia, $finDia)
    {
        $horarioTurno = $horario['turnoOficial'];
        $sumTolerancia = $toleranciaEntrada + $toleranciaSalida;

        $esperadoIn = Carbon::create($horario['date'] . ' ' . $horarioTurno['entry_time']);
        $realIn = Carbon::create($horario['date'] . ' ' . $horario['entry_time_one']);
        $esperadoOut = Carbon::create($horario['leave_date'] . ' ' . $horarioTurno['leave_time']);
        $realOut = Carbon::create($horario['leave_date'] . ' ' . $horario['leave_time_one']);

        $cantHorasHorario = new stdObject([
            'horas' => intval($esperadoIn->diff($esperadoOut)->format('%H')),
            'minutos' => intval($esperadoIn->diff($esperadoOut)->format('%i')),
            'segundos' => intval($esperadoIn->diff($esperadoOut)->format('%s'))
        ]);

        $cantHorasDiario = new stdObject([  //horas trabajadas
            'horas' => intval($realIn->diff($realOut)->format('%H')),
            'minutos' => intval($realIn->diff($realOut)->format('%i')),
            'segundos' => intval($realIn->diff($realOut)->format('%s'))
        ]);

        $diferenciaReal = new stdObject([
            'horas' => $cantHorasDiario->horas - $cantHorasHorario->horas,
            'minutos' => $cantHorasDiario->minutos - $cantHorasHorario->minutos,
            'segundos' => $cantHorasDiario->segundos - $cantHorasHorario->segundos
        ]);

        $intSalida = $this->redondearHora($diferenciaReal, $sumTolerancia);
        $cantDiarios = $this->redondearHora($cantHorasDiario, $sumTolerancia);
        $sali = $realOut;
        $horasExtras = $this->clasificacionHorasExtras($intSalida, $cantDiarios, $horario, $sali, $comienzoDia, $finDia);

        $horasRecargo = new stdObject([
            "hrndf" => 0,
            "hrn" => 0,
            "hrddf" => 0
        ]);
        if ($realOut > $finDia) {
            $recargo = ($esperadoOut->diffInHours($finDia));
            $horasRecargo = $this->clasificacionHorasRecargo($recargo, $horario, $realOut, $comienzoDia, $finDia);
        }

        $diferencias = new stdObject([
            'date' => $horario['date'],
            'day' => $horario['nombreDia'],
            'extras' => $intSalida,
            'hour_in' => $realIn->toDateTimeString(),
            'hour_out' => $realOut->toDateTimeString(),
            'hours_extra' => $horasExtras,
            'hours_recharge' => $horasRecargo,
            'hours_schedule' => $cantHorasHorario,
            'schedule_in' => $esperadoIn->toDateTimeString(),
            'schedule_out' => $esperadoOut->toDateTimeString()
        ]);

        return $diferencias;
    }




    //convierte strings a Carbon
    private function getLeaveDate($laborado)
    {
        $date = '';
        if ($laborado['leave_time_one']) {
            $date = $laborado['leave_date'] . $laborado['leave_time_one'];
        } elseif ($laborado['breack_time_one'] && !$laborado['breack_time_two']) {
            $date = $laborado['breack_one_date'] . $laborado['breack_time_one'];
        } elseif (isset($laborado['launch_time_one']) && $laborado['launch_time_one']) {
            $date = $laborado['launch_one_date'] . $laborado['launch_time_one'];
        }

        return Carbon::parse($date);
    }

    /**
     * este parece útil
     */
    public function workedTime($laborado, $asistencia, $salida)
    {

        $diffTotal = 0;
        //si tiene hora 1 de launch
        if ($laborado['leave_time_one']) {
            $diffTotal += $asistencia->diffInMinutes($salida);
        } elseif ($laborado['breack_time_one'] && !$laborado['breack_time_two']) {
            $launchEntry = Carbon::parse($laborado['breack_one_date'] . $laborado['breack_time_one']);
            $diffTotal = $asistencia->diffInMinutes($launchEntry);
        } elseif (isset($laborado['launch_time_one']) && $laborado['launch_time_one']) {
            $launchEntry = Carbon::parse($laborado['launch_one_date'] . $laborado['launch_time_one']);
            $diffTotal = $asistencia->diffInMinutes($launchEntry);
        }
        //si tiene hora 2 de launch
        if (isset($laborado['launch_time_two']) && $laborado['launch_time_two']) {
            $launchEntry = Carbon::parse($laborado['launch_two_date'] . $laborado['launch_time_two']);
            $launchLe = Carbon::parse($laborado['breack_two_date'] . $laborado['breack_time_two']);
            if ($salida) {
                $diffTotal += $launchEntry->diffInMinutes($salida);
            }
        }
        if (isset($laborado['entry_time_two']) && Request()->get('tipo') == 'Fijo' && $laborado['entry_time_two']) {
            $launchEntry = Carbon::parse($laborado['date'] . $laborado['entry_time_two']);
            $launchLe = Carbon::parse($laborado['date'] . $laborado['leave_time_one']);
            $diffTotal = $launchEntry->diffInMinutes($launchLe);
        }
        return $diffTotal;
    }

    //no le veo utiliddad a esta función _dy
    public function Asistencia($fecha, $horaEntrada, $horaSalida)
    {
        $asistencia = Carbon::parse($fecha . $horaEntrada);
        $salida = Carbon::parse($fecha . $horaSalida);
        $fechaSalida = Carbon::parse($fecha . $horaSalida);

        if (($horaSalida >= '00:00:00' && $horaSalida < $horaEntrada)) {
            $salida->addDay();
            $fechaSalida->addDay();
        }

        return [$asistencia, $salida, $fechaSalida];
    }

    public function setTiempoExtra($cantidadEnMinutos)
    {
        $this->tiempoParaExtras - $cantidadEnMinutos;
    }



    public function prueba(Request $request)  //esto se borra
    {
        $fechaInicio = "2022-06-12";
        $fechaFin = "2022-06-18";

        $filtroDiarioFecha = function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('date', [$fechaInicio, $fechaFin])->orderBy('date');
        };
        $funcionario = $this->funcionarioFijo($filtroDiarioFecha, $fechaInicio, $fechaFin);

        /* $fecha = "2022-06-13";
        $hora = Carbon::create('2022-06-13 06:40:43');
        $diaIni = '2022-06-13 06:01:00';
        $diaFin = '2022-06-13 21:01:00'; */
        $fecha = $request->fecha;
        $hora = Carbon::create($request->hora);
        $diaIni = $request->diaIni;
        $diaFin = $request->diaFin;
        //dd($this->tipoHora($fecha, $hora, $diaIni, $diaFin));

        return $this->calcularExtras($funcionario);
    }

    public function funcionarioRotativo($filtroDiarioFecha)
    {
        $translateService = new TranslateService();
        $funcionario = Person::with(['diariosTurnoRotativo' => $filtroDiarioFecha])
            ->with(['horariosTurnoRotativo' => $filtroDiarioFecha])
            ->with('companyWorked')
            ->find(request()->get('id'))->toArray();

        if (isset($funcionario['horarios_turno_rotativo'])) {
            $funcionario['days_work'] = $funcionario['horarios_turno_rotativo'];
            foreach ($funcionario['diarios_turno_rotativo'] as $diario) {
                foreach ($funcionario['horarios_turno_rotativo'] as $ky => $turno) {
                    if ($turno['date'] == $diario['date']) {
                        $diario['nombreDia'] = $translateService->translateDay(Carbon::create($diario['date'])->englishDayOfWeek);
                        $diario['turnoOficial'] = RotatingTurn::find($turno['rotating_turn_id']);
                        array_push($funcionario['days_work'][$ky], ['day' => $diario]);
                    }
                }
            }
            unset($funcionario['diarios_turno_rotativo']);
        } else {
            $funcionario['days_work'] = array();
            //return response()->json(['msg'=> 'Horario no asignado']);
        }
        return $funcionario;
    }

    public function funcionarioFijo($filtroDiarioFecha, $fechaInicio, $fechaFin)
    {
        try {
            $translateService = new TranslateService();

            $funcionario = Person::with([
                'diariosTurnoFijo' => function ($q) use ($filtroDiarioFecha) {
                    $q->select('*')->where($filtroDiarioFecha);
                },
                'contractultimate' => function ($q) {
                    $q->select('*')->with('fixedTurn');
                }
            ])->with('companyWorked')
                ->find(request()->get('id'))->toArray();
            $funcionario['fecha_inicio'] = Carbon::create($fechaInicio)->locale('es_ES');
            $funcionario['fecha_fin'] = Carbon::create($fechaFin)->locale('es_ES');

            if (isset($funcionario['contractultimate']['fixed_turn']['horarios_turno_fijo'])) {
                $funcionario['turno_fijo'] = $funcionario['contractultimate']['fixed_turn'];
                unset($funcionario['contractultimate']);

                $funcionario['days_work'] = $funcionario['turno_fijo']['horarios_turno_fijo'];
                //dd($funcionario['days_work']);
                //aca le está poniendo la fecha que no corresponde al día de la semana
                $dias = $funcionario['fecha_fin']->diffInDays($funcionario['fecha_inicio']);
                $rgoFecha = array();
                for ($i = 0; $i <= $dias; $i++) {
                    $fecha = Carbon::create($fechaInicio)->addDays($i);
                    array_push($rgoFecha, $fecha);
                }
                //dd($rgoFecha);
                /*  foreach ($funcionario['days_work'] as $clave => $dayWork) {
                    $funcionario['days_work'][$clave]['date'] = $fecha;
                } */

                /**
                 * Si no hay diarios de entrada, este arreglo es vacío
                 * dd($funcionario['diarios_turno_fijo']);
                 */
                //dd($funcionario['diarios_turno_fijo']);
                foreach ($funcionario['diarios_turno_fijo'] as $diario) {
                    foreach ($funcionario['turno_fijo']['horarios_turno_fijo'] as $ky => $turno) {
                        if ($turno['day'] == $translateService->translateDay(Carbon::create($diario['date'])->englishDayOfWeek)) {
                            $funcionario['days_work'][$ky]['date'] = Carbon::create($diario['date']); //esta es la forma óptima
                            //$funcionario['days_work'][$ky]['day'] = $diario; //esta es la forma óptima
                            array_push($funcionario['days_work'][$ky], ['day' => $diario]); //asín lo está leyendo el front
                        }
                    }
                }
                unset($funcionario['diarios_turno_fijo']);
            } else {
                $funcionario['days_work'] = array();
                //return response()->json(['msg'=> 'Horario no asignado']);
            }

            return $funcionario;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}


/*
Turno fijo
         $funcionario =  Person::with(['diariosTurnoRotativo' => $filtroDiarioFecha])
->with(['horariosTurnoRotativo' => $filtroDiarioFecha])
->find(request()->get('id'))->toArray(); */

/*  $funcionario =  Person::with(
['diariosTurnoFijo' => $filtroDiarioFecha],
)
->with('contractultimate', function ($q) {
    $q->select('*')
        ->with('fixedTurn.horariosTurnoFijo');
})
    ->find(request()->get('id')); */
