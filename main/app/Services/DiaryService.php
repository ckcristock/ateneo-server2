<?php

namespace App\Services;

use App\Models\DiarioTurnoFijo;
use App\Models\DiarioTurnoRotativo;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class DiaryService
{
    /**Funciones de estadisticas */
    public static function getPeople($id, $dates, $company_id)
    {
        $compare = Request()->get('turn_type') == 'Rotativo' ? 'rotating_turn_diaries' : 'fixed_turn_diaries';
        return DB::table("people as p")
            ->join("work_contracts as w", function ($join) {
                $join->on(
                    "p.id",
                    "=",
                    "w.person_id"
                )->where('w.liquidated', 0);
            })
            ->join("positions as ps", "ps.id", "=", "w.position_id")
            ->where("ps.dependency_id", $id)
            ->where("w.company_id", $company_id)
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('p.id', $fill);
            })
            ->whereExists(function ($query) use ($dates, $compare) {
                $query
                    ->select(DB::raw(1))
                    ->from("$compare as la")
                    ->whereColumn("la.person_id", "p.id")
                    ->whereBetween(DB::raw("DATE(la.date)"), $dates);
            })
            ->where('p.status', '!=', 'liquidado')

            ->select("p.first_name", "p.first_surname", "p.id", "p.image", DB::raw("CONCAT_WS(' ', first_name, second_name, first_surname, second_surname) as completed_name"))
            ->orderBy("p.first_name")
            ->get();
    }
    public static function getDiaries($personId, $dates)
    {
        return DiarioTurnoFijo::alias("la")->with('edit')
            ->select("*")
            ->selectRaw('
			(IF(FORMAT((TIME_TO_SEC(leave_time_one) - TIME_TO_SEC(entry_time_one))/3600,2)>=0,
			FORMAT((TIME_TO_SEC(leave_time_one) - TIME_TO_SEC(entry_time_one))/3600,2),0) +
			(IF(FORMAT((TIME_TO_SEC(leave_time_two) - TIME_TO_SEC(entry_time_two))/3600,2)>=0,
			FORMAT((TIME_TO_SEC(leave_time_two) - TIME_TO_SEC(entry_time_two))/3600,2),0))) as working_hours')
            ->where("la.person_id", $personId)
            ->whereBetween(DB::raw("DATE(la.date)"), $dates)
            ->get();
    }


    //Consulta Turnos rotativos

    public static function getDiariesRotative2($personId, $dates)
    {
        return DB::table("rotating_turn_diaries as la")
            ->select("la.*")
            ->join('rotating_turns as r', 'r.id', '=', 'la.rotating_turn_id')
            ->selectRaw('r.entry_time as entry_time_real , r.leave_time as leave_time_real,
			            r.launch_time as launch_time_real,  r.launch_time_two as launch_time_two_real,
			            r.breack_time as breack_time_real , r.breack_time_two as breack_time_two_real')
            ->selectRaw('
			ROUND( (
				TIMESTAMPDIFF(
				SECOND,CONCAT(la.date," ",la.entry_time_one)
				,CONCAT(IFNULL(la.leave_date, la.date)," ",la.leave_time_one)
				) -
				IFNULL(TIMESTAMPDIFF(
					SECOND,CONCAT(la.date," ",la.launch_time_one)
					,CONCAT(IFNULL(la.leave_date, la.date)," ",la.launch_time_two)
				),0)
			)
			/3600 ,2 ) as working_hours')
            ->where("la.person_id", $personId)

            ->whereBetween(DB::raw("DATE(la.date)"), $dates)
            ->get();
    }

    public static function getDiariesRotative($personId, $dates)
    {
        return DiarioTurnoRotativo::alias("la")
            ->with('edit')
            ->select("la.*")
            ->join('rotating_turns as r', 'r.id', '=', 'la.rotating_turn_id')
            ->selectRaw('r.entry_time as entry_time_real , r.leave_time as leave_time_real,
			            r.launch_time as launch_time_real,  r.launch_time_two as launch_time_two_real,
			            r.breack_time as breack_time_real , r.breack_time_two as breack_time_two_real')
            ->selectRaw('
			ROUND( (
				TIMESTAMPDIFF(
				SECOND,CONCAT(la.date," ",la.entry_time_one)
				,CONCAT(IFNULL(la.leave_date, la.date)," ",la.leave_time_one)
				) -
				IFNULL(TIMESTAMPDIFF(
					SECOND,CONCAT(la.date," ",la.launch_time_one)
					,CONCAT(IFNULL(la.leave_date, la.date)," ",la.launch_time_two)
				),0)
			)
			/3600 ,2 ) as working_hours')
            ->where("la.person_id", $personId)
            ->whereBetween(DB::raw("DATE(la.date)"), $dates)
            ->get();
    }

    public static function getDiariesRotativeDowload($dates)
    {
        $company_id = Person::find(Auth()->user()->person_id)->company_worked_id;
        return DB::table("rotating_turn_diaries as la")
            ->join('rotating_turns as r', 'r.id', '=', 'la.rotating_turn_id')
            ->join('people as p', 'p.id', 'la.person_id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->join('positions as ps', 'ps.id', '=', 'w.position_id')
            ->join('dependencies as de', 'de.id', '=', 'ps.dependency_id')
            ->selectRaw(
                '
                    de.name,
                    CONCAT(p.first_name, " ", p.first_surname),
                    DATE(la.date) as date,
                    IFNULL( la.entry_time_one , "Sin Reporte"),
                    IFNULL( la.launch_time_one, "Sin Reporte"),
                    IFNULL( la.launch_time_two, "Sin Reporte"),
                    IFNULL( la.breack_time_one , "Sin Reporte"),
                    IFNULL( la.breack_time_two, "Sin Reporte"),
                    IFNULL( la.leave_time_one, "Sin Reporte"),
                    ROUND( (
                        TIMESTAMPDIFF(
                        SECOND,CONCAT(date," ",entry_time_one)
                        ,CONCAT(leave_date," ",leave_time_one)
                        ) -
                        IFNULL(TIMESTAMPDIFF(
                            SECOND,CONCAT(date," ",la.launch_time_one)
                            ,CONCAT(leave_date," ",la.launch_time_two)
                        ),0)
                    )
                    /3600 ,2 ) as working_hours
                '
            )
            ->whereBetween(DB::raw("DATE(la.date)"), $dates)
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('la.person_id', $fill);
            })
            ->where('w.company_id', $company_id)
            ->when(Request()->get('dependency_id'), function ($q, $fill) {
                $q->where('de.id', $fill);
            })
            ->when(Request()->get('group_id'), function ($q, $fill) {
                $q->where('de.group_id', $fill);
            })
            ->get();
    }
    public static function getDiariesFixedDowload($dates)
    {
        $company_id = Person::find(Auth()->user()->person_id)->company_worked_id;
        return DB::table("fixed_turn_diaries as la")
            ->join('fixed_turns as r', 'r.id', '=', 'la.fixed_turn_id')
            ->join('people as p', 'p.id', 'la.person_id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->join('positions as ps', 'ps.id', '=', 'w.position_id')
            ->join('dependencies as de', 'de.id', '=', 'ps.dependency_id')
            ->selectRaw(
                '
                    de.name,
                    CONCAT(p.first_name, " ", p.first_surname),
                    DATE(la.date) as date,
                    IFNULL( la.entry_time_one, "Sin Reporte"),
                    IFNULL(la.leave_time_one , "Sin Reporte"),
                    IFNULL(la.entry_time_two , "Sin Reporte"),
                    IFNULL(la.leave_time_two, "Sin Reporte"),
                    (IF(FORMAT((TIME_TO_SEC(leave_time_one) - TIME_TO_SEC(entry_time_one))/3600,2)>=0,
                    FORMAT((TIME_TO_SEC(leave_time_one) - TIME_TO_SEC(entry_time_one))/3600,2),0) +
                    (IF(FORMAT((TIME_TO_SEC(leave_time_two) - TIME_TO_SEC(entry_time_two))/3600,2)>=0,
                    FORMAT((TIME_TO_SEC(leave_time_two) - TIME_TO_SEC(entry_time_two))/3600,2),0))) as working_hours
                '
            )
            ->whereBetween(DB::raw("DATE(la.date)"), $dates)
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('la.person_id', $fill);
            })
            ->where('w.company_id', $company_id)
            ->when(Request()->get('dependency_id'), function ($q, $fill) {
                $q->where('de.id', $fill);
            })
            ->when(Request()->get('group_id'), function ($q, $fill) {
                $q->where('de.group_id', $fill);
            })
            ->get();
    }
}
