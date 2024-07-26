<?php

namespace App\Services;

use App\Traits\ConsumeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentService
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
        $queryString = '{"MaxResults":25,"MatchAll":true,"PetitionID":null,"RoomID":null,"SiteID":null,"InstitutionID":28,"Institutions":null,"PatientInternalID":null,"PatientID":null,"PatientName":null,"ExamID":null,"OrderID":null,"Type":null,"Status":null,"ReportStatus":null,"Date":"/Date(1625893200000-0500)/","Date2":"/Date(1626065999999-0500)/","DateMatch":null,"ReminderSent":null,"Priority":null,"AssignedDr":null,"Specialities":null,"IncludeUnassigned":false,"ExcludeID":null}';
        return $this->performRequest('GET', '/service/AppointmentService.svc/appointments', $queryString);
    }

    static public function index()
    {
        $page = request()->get('page', 1);
        $pageSize = request()->get('pageSize', 20);

        return DB::table('appointments')
            ->join('call_ins', 'call_ins.id', '=', 'appointments.call_id')
            ->join('patients', 'patients.identifier', '=', 'call_ins.Identificacion_Paciente')
            ->join('spaces', 'spaces.id', '=', 'appointments.space_id')
            ->join('people', 'people.id', '=', 'spaces.person_id')
            ->join('agendamientos', 'agendamientos.id', '=', 'spaces.agendamiento_id')
            ->join('type_appointments', 'type_appointments.id', 'agendamientos.type_agenda_id')
            ->join('sub_type_appointments', 'sub_type_appointments.id', '=', 'agendamientos.type_appointment_id')
            ->join('specialities', 'specialities.id', '=', 'agendamientos.speciality_id')
            ->join('administrators', 'administrators.id', '=', 'patients.eps_id')
            ->when((Request()->get('identifier') && Request()->get('identifier') != 'null'), function ($query) {
                $query->where('call_ins.Identificacion_Paciente', Request()->get('identifier'));
            })
            ->when((Request()->get('person_id') && Request()->get('person_id') != 'nul'), function ($query) {
                $query->where('people.id', '=', Request()->get('person_id'));
            })
            ->when((Request()->get('state') && Request()->get('state') != 'null'), function ($query) {
                $query->where('appointments.state', '=', Request()->get('state'));
            })
            ->when((Request()->get('type_appointment_id') && Request()->get('type_appointment_id') != 'null'), function ($query) {
                $query->where('type_appointments.id', Request()->get('type_appointment_id'));
            })
            ->when((Request()->get('sub_type_appointment_id') && Request()->get('sub_type_appointment_id') != 'null'), function ($query) {
                $query->where('sub_type_appointments.id', Request()->get('sub_type_appointment_id'));
            })
            ->when((Request()->get('speciality_id') && Request()->get('speciality_id') != 'null'), function ($query) {
                $query->where('agendamientos.speciality_id', Request()->get('speciality_id'));
            })
            ->when((Request()->get('company_id') && Request()->get('company_id') != 'null'), function ($query) {
                $query->where('agendamientos.ips_id', Request()->get('company_id'));
            })
            ->when(Request()->has('space_date') && Request()->get('space_date') != 'null', function ($query) {
                $startOfDay = \Carbon\Carbon::parse(Request()->get('space_date'))->startOfDay();
                $endOfDay = \Carbon\Carbon::parse(Request()->get('space_date'))->endOfDay();
                $query->whereBetween('spaces.hour_start', [$startOfDay, $endOfDay]);
            })
            ->when((Request()->get('eps') && Request()->get('eps') != 'null'), function ($query) {
                $query->where('patients.eps_id', Request()->get('eps'));
            })
            ->select(
                'appointments.*',
                DB::raw('DATE_FORMAT(spaces.hour_start, "%Y-%m-%d %h:%i %p") As hour_start'),
                DB::raw('Concat_ws(" ", people.first_name, people.first_surname) as profesional_name'),
                DB::raw('Concat_ws(" ", patients.firstname,  patients.surname, patients.identifier) as patient_name'),
                'specialities.name as speciality',
                'patients.phone as phone',
                'appointments.state as state',
                'administrators.name as eps'
            )
            ->orderByDesc('spaces.hour_start')
            ->paginate($pageSize, '*', 'page', $page);

    }
    static public function toMigrate(Request $request)
    {
        $data = $request->only(['page', 'pageSize']);
        $page = $data['page'] ?? 1;
        $pageSize = $data['pageSize'] ?? 10;

        return DB::table('appointments')
            ->join('call_ins', 'call_ins.id', '=', 'appointments.call_id')
            ->join('patients', 'patients.identifier', '=', 'call_ins.Identificacion_Paciente')
            ->join('spaces', 'spaces.id', '=', 'appointments.space_id')
            ->join('agendamientos', 'agendamientos.id', '=', 'spaces.agendamiento_id')
            ->join('people', 'people.id', '=', 'agendamientos.person_id')
            ->join('type_appointments', 'type_appointments.id', '=', 'agendamientos.type_agenda_id')
            ->join('sub_type_appointments', 'sub_type_appointments.id', '=', 'agendamientos.type_appointment_id')
            ->join('specialities', 'specialities.id', '=', 'agendamientos.speciality_id')
            ->select(
                'appointments.*',
                DB::raw('DATE_FORMAT(spaces.hour_start, "%Y-%m-%d %h:%i %p") AS hour_start'),
                DB::raw('CONCAT_WS(" ", people.first_name, people.first_surname) AS profesional_name'),
                DB::raw('CONCAT_WS(" ", patients.firstname,  patients.surname, patients.identifier) AS patient_name'),
                'specialities.name AS speciality',
                'patients.phone AS phone'
            )
            ->whereNull('globo_id')
            ->whereNull('on_globo')
            ->whereNotNull('space_id')
            ->where('appointments.state', 'agendado')
            ->when($request->filled('identifier'), function ($query, $fill) {
                $query->where('call_ins.Identificacion_Paciente', $fill);
            })
            ->when($request->filled('person_id'), function ($query, $fill) {
                $query->where('people.id', $fill);
            })
            ->when($request->filled('type_appointment_id'), function ($query, $fill) {
                $query->where('type_appointments.id', $fill);
            })
            ->when($request->filled('sub_type_appointment_id'), function ($query, $fill) {
                $query->where('sub_type_appointments.id', $fill);
            })
            ->when($request->filled('speciality_id'), function ($query, $fill) {
                $query->where('agendamientos.speciality_id', $fill);
            })
            ->when($request->filled('company_id'), function ($query, $fill) {
                $query->where('agendamientos.ips_id', $fill);
            })
            ->when($request->filled('location_id'), function ($query, $fill) {
                $query->where('agendamientos.location_id', $fill);
            })
            ->when($request->filled('space_date'), function ($query, $fill) {
                $query->whereDate('spaces.hour_start', $fill);
            })
            ->orderBy('spaces.hour_start', 'asc')
            ->paginate($pageSize, '*', 'page', $page);
    }
}
