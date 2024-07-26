<?php

namespace App\Services;

use App\Models\Laboratories;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;

class LaboratoryService
{
    static public function getDotation()
    {
        return DB::table('laboratories')
            ->join('patients', 'laboratories.patient', '=', 'patients.id')
            ->join('municipalities', 'patients.municipality_id', '=', 'municipalities.id')
            ->join('eps', 'patients.eps_id', '=', 'eps.id')
            ->select(
                'municipalities.name as name_city',
                DB::raw("CONCAT(patients.firstname, ' ', patients.middlename, ' ', patients.surname, ' ', patients.secondsurname) AS name_patient"),
                'eps.name as name_eps',
                'laboratories.*'
            )
            ->orderByDesc('laboratories.created_at')
            ->get();
    }
}
