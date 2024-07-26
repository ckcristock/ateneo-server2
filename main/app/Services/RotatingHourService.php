<?php

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;

class RotatingHourService
{
    /**Funciones de estadisticas */
    public static function getPeople($id, $company_id)
    {
        // ? Recibe el company_id ya filtrado
        $today = Carbon::now();
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
            ->where("w.turn_type", 'Rotativo')
            ->where(function ($query) use ($today) {
                $query->where("w.date_end", null)
                    ->orWhere("w.date_end", '>', $today);
            })
            ->where("w.company_id", $company_id)
            ->where("p.status", 'Activo')
            ->when(Request()->get('person'), function ($q, $fill) {
                $q->where(DB::raw('concat(p.first_name, " ",p.first_surname)'), 'like', '%' . $fill . '%');
            })
            ->select("p.first_name", "p.first_surname", "p.id", "p.image")->orderBy("p.first_name")
            ->get()->toArray();
    }
    public static function getHours($personId)
    {
        return DB::table("rotating_turn_hours")
            ->select("*")
            ->where("person_id", $personId)
            ->get();
    }
    //Consulta Turnos rotativos
}
