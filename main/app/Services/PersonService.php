<?php

namespace App\Services;

use App\Models\Person;
use App\Traits\ConsumeService;
use Illuminate\Support\Facades\DB;

class PersonService
{
    use ConsumeService;

    /**
     * The base uri to be used to consume the authors service
     * @var string
     */
    public $baseUri;
    public $token;

    /**
     * The secret to be used to consume the authors service
     * @var string
     */
    public $secret;


    public function __construct()
    {
        $this->baseUri = env('BASE_URI');
        $this->token = env('TOKEN');
    }

    /**
     * Get the full list of authors from the authors service
     * @return string
     */
    public function get()
    {
        /*  Para medicos persones
         https://mmedical.mmgroup.com.co/service/UserService.svc/doctors?queryOptions={
            "MaxResults":0,
            "MatchAll":true,
            "RoleID":3,
            "RoleIDs":null,
            "InstitutionID":32,
            "InstitutionIDs":null,
            "SpecialityID":,
            "ExamGroupID":null,
            "IsParticular":null,
            "IsInstitutional":true}
            &token=
            C0T4Of8t2UjOTTfo4g5Dj1hw4kEyVKoPqolm2A1XrHeR%2FsvFVSYEDlNOs%2BHk7xvccWRvdcPBzzSo8nffrBp
            iHUVbgzoQwI7Pq1JyvZr5Sw9%2F8X3UOKfcdIuDloUMmkeEckY3PZ5yFrLhDjIsFDb7%2Bo9Or7Hd9O2uc%2FVAL
            PZTTbjj8c75XJw0ogT2Wj%2FiMYPH%2FPo4ONseUZuqw2ZmR1yaKA%3D%3D */
        $queryString = '{
            "MaxResults":0,
            "MatchAll":true,
            "RoleID":3,
            "RoleIDs":null,
            "InstitutionID":null,
            "InstitutionIDs":null,
            "SpecialityID":null,
            "ExamGroupID":null,
            "IsParticular":null,
            "IsInstitutional":true
        }';
        return $this->performRequest('GET', '/service/UserService.svc/doctors', $queryString);
        /* $queryString = '{
            "MaxResults":500,
            "MatchAll":true,
            "PatientInternalID":null,
            "PatientBirthDate":null,
            "StartCreatedDate":null,
            "EndCreatedDate":null,
            "Institutions":null,
            "IncludeInstitutions":null
        }';
        $queryString = '{
            "MaxResults":1000,
            "MatchAll":true,
            "RoleID":null,
            "RoleIDs":null,
            "InstitutionID":null,
            "InstitutionIDs":null,
            "SpecialityID":null,
            "ExamGroupID":null,
            "IsParticular":null,
            "IsInstitutional":true
        }';
        return $this->performRequest('GET', 'service/UserService.svc/doctors', $queryString);
        return $this->performRequest('GET', 'service/PatientService.svc/patients', $queryString); */
    }

    public static function getPeople($data = [])
    {
        $company = Person::find(Auth()->user()->person_id)->company_worked_id;
        return DB::table('people as p')
            ->select(
                'p.id',
                'p.identifier',
                'p.image',
                'p.status',
                'p.full_name',
                'p.first_surname',
                'p.first_name',
                'pos.name as position',
                'd.name as dependency',
                'p.id as value',
                DB::raw('CONCAT_WS(" ",first_name,first_surname) as text '),
                'c.name as company',
                DB::raw('w.id AS work_contract_id')
            )
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->where('w.liquidated', 0);
            })
            ->join('companies as c', 'c.id', '=', 'w.company_id')
            ->join('positions as pos', 'pos.id', '=', 'w.position_id')
            ->join('dependencies as d', 'd.id', '=', 'pos.dependency_id')
            ->join('groups as g', 'g.id', '=', 'd.group_id')
            ->where('p.status', 'activo')
            ->where('w.company_id', $company)
            ->when(key_exists('name', $data), function ($q) use ($data) {
                $q->where('p.identifier', 'like', '%' . $data['name'] . '%')
                    ->orWhere(DB::raw('concat(p.first_name," ",p.first_surname)'), 'LIKE', '%' . $data['name'] . '%');
            })
            ->when(key_exists('dependencies', $data), function ($q) use ($data) {
                $q->whereIn('d.id', $data['dependencies']);
            })
            ->when(key_exists('groups', $data), function ($q) use ($data) {
                $q->whereIn('g.id', $data['groups']);
            })
            ->when(key_exists('status', $data), function ($q) use ($data) {
                $q->whereIn('p.status', $data['status']);
            })
            ->when($data['dependency_id'], function ($q) use ($data) {
                $q->whereIn('p.status', $data['dependency_id']);
            })
            ->get();
    }

    public static function getPeopleE($data)
    {
        $company = Person::find(Auth()->user()->person_id)->company_worked_id;
        return
            Person::whereHas('contractultimate', function ($query) use ($company) {
                $query->where('company_id', $company);
            })
                ->when($data['dependency_id'], function ($query, $fill) {
                    $query->whereHas('contractultimate.position.dependency', function ($query) use ($fill) {
                        $query->where('id', $fill);
                    });
                })
                ->when($data['group_id'], function ($query, $fill) {
                    $query->whereHas('contractultimate.position.dependency.group', function ($query) use ($fill) {
                        $query->where('id', $fill);
                    });
                })
                ->where('status', 'activo')
                ->get(['id']);
    }


    public static function funcionarioTurno($personId, $dia, $hoy, $ayer)
    {
        $funcionario = Person::where('personId', $personId)
            /* ->with('cargo') */
            ->with('contractultimate')
            ->with('contractultimate.fixedTurn')
            ->with('contractultimate.fixedTurn.horariosTurnoFijo')
            ->with([
                'diariosTurnoFijo' => function ($query) use ($hoy) {
                    $query->where('date', '=', $hoy);
                }
            ])->with([
                    'turnoFijo.horariosTurnoFijo' => function ($query) use ($dia) {
                        $query->where('day', '=', $dia);
                    }
                ])->with([
                    'diariosTurnoRotativoAyer' => function ($query) use ($ayer) {
                        $query->with('turnoRotativo')->where('date', '=', $ayer)->whereNull('leave_date');
                    }
                ])->with([
                    'diariosTurnoRotativoHoy' => function ($query) use ($hoy) {
                        $query->with('turnoRotativo')->where('date', '=', $hoy);
                    }
                ])->with(
                [
                    'horariosTurnoRotativo' => function ($query) use ($hoy) {
                        $query->with('turnoRotativo')->where('date', '=', $hoy);
                    }
                ]
            )->first();

        if (!$funcionario) {
            return false;
        }
        return $funcionario;
    }
}
