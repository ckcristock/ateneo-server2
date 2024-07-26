<?php

namespace App\Services;

use App\Models\Person;
use Illuminate\Support\Facades\DB;

class LateArrivalService
{
    /**Funciones de estadisticas */
    public static function getLates($dates)
    {
        $company_id = Person::find(Auth()->user()->person_id)->company_worked_id;
        return DB::table('late_arrivals as l')
            ->join('people as p', 'l.person_id', '=', 'p.id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->whereBetween(DB::raw('DATE(l.created_at)'), $dates)
            ->where('w.company_id', $company_id)
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('p.id', $fill);
            })
            ->selectRaw('count(*) as total')
            ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(l.real_entry) - TIME_TO_SEC(l.entry))) AS time_diff_total')
            ->first();
    }

    public static function getAllByDependecies($dates)
    {
        $company_id = Person::find(Auth()->user()->person_id)->company_worked_id;
        return DB::table('late_arrivals as l')
            ->join('people as p', 'l.person_id', '=', 'p.id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->join('positions as ps', 'ps.id', '=', 'w.position_id')
            ->join('dependencies as d', 'd.id', '=', 'ps.dependency_id')
            ->where('w.company_id', $company_id)
            ->whereBetween(DB::raw('DATE(l.created_at)'), $dates)
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('p.id', $fill);
            })
            ->selectRaw('count(*) as total, d.name')
            ->groupBy('d.id')
            ->get();
    }

    public static function allMarcations($dates)
    {
        $company_id = Person::find(Auth()->user()->person_id)->company_worked_id;
        return DB::table('marcations as m')
            ->join('people as p', 'm.person_id', '=', 'p.id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->where('w.company_id', $company_id)
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('p.id', $fill);
            })
            ->whereBetween(DB::raw('DATE(date)'), $dates)
            ->count();
    }

    public static function getAllLinear($dates)
    {
        $company_id = Person::find(Auth()->user()->person_id)->company_worked_id;
        return DB::table('late_arrivals as l')
            ->join('people as p', 'l.person_id', '=', 'p.id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->where('w.company_id', $company_id)
            ->whereBetween(DB::raw('DATE(l.created_at)'), $dates)
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('p.id', $fill);
            })
            ->selectRaw('count(*) as total')
            ->selectRaw('DAY(l.created_at) as day ')
            ->groupBy(DB::raw('DATE(l.created_at) '))
            ->get();
    }

    /**End Funciones de estadisticas */


    /**Arrivals */

    public static function getLastArrivals($personId, $dates)
    {
        // ? Aquí ya está recibiendo solo las personas del company_id
        return DB::table('late_arrivals as la')
            ->select('*')
            ->selectRaw('TIMEDIFF(la.real_entry,la.entry) AS entry_diff')
            ->where('la.person_id', $personId)
            ->whereBetween(DB::raw('DATE(la.created_at)'), $dates)
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('la.person_id', $fill);
            })
            ->get();
    }

    public static function getPeople($id, $dates, $company_id)
    {
        // ? Recibe el company_id desde la función
        return DB::table('people as p')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->join('positions as ps', 'ps.id', '=', 'w.position_id')
            ->where('ps.dependency_id', $id)
            ->where('w.company_id', $company_id)
            ->whereExists(function ($query) use ($dates) {
                $query->select(DB::raw(1))
                    ->from('late_arrivals as la')
                    ->whereColumn('la.person_id', 'p.id')
                    ->whereBetween(DB::raw('DATE(la.created_at)'), $dates);
            })
            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('p.id', $fill);
            })
            ->select('p.first_name', 'p.first_surname', 'p.id', 'p.image')
            ->orderBy("p.first_name")
            ->get();
    }

    public static function getPeopleDownload($dates)
    {
        $company_id = Person::find(Auth()->user()->person_id)->company_worked_id;
        return DB::table('late_arrivals as la')
            ->join('people as p', 'p.id', 'la.person_id')
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->join('positions as ps', 'ps.id', '=', 'w.position_id')
            ->join('dependencies as de', 'de.id', '=', 'ps.dependency_id')
            ->select(
                'de.name',
                DB::raw('CONCAT(p.first_name," ", p.first_surname)'),
                DB::raw('DATE(la.date) as date'),
                'la.entry',
                'la.real_entry'
            )
            ->selectRaw('TIMEDIFF(la.real_entry,la.entry) AS entry_diff')
            ->where('w.company_id', $company_id)
            ->whereBetween(DB::raw('DATE(la.created_at)'), $dates)

            ->when(Request()->get('person_id'), function ($q, $fill) {
                $q->where('la.person_id', $fill);
            })

            ->when(Request()->get('dependency_id'), function ($q, $fill) {
                $q->where('de.id', $fill);
            })
            ->when(Request()->get('group_id'), function ($q, $fill) {
                $q->where('de.group_id', $fill);
            })
            ->get();
    }
}
