<?php

namespace App\Http\Controllers;
use App\Exports\PatientsTemplateExport;
use App\Http\Requests\PatientSaveRequest;
use App\Models\CallIn;
use App\Models\Patient;
use App\Traits\ApiResponser;
use App\Traits\manipulateDataFromExternalService;
use Illuminate\Http\Request;
use App\Http\Resources\PatientResource;
use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Imports\PatientsImport;
use App\Models\BodegaNuevo;
use App\Models\CompanyConfiguration;
use App\Models\Impuesto;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PatientController extends Controller
{

    use ApiResponser, manipulateDataFromExternalService;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $patient = Patient::query();
            $patient->when(request()->input('search') != '', function ($q) {
                $q->where(function ($query) {
                    $query->where('identifier', 'like', '%' . request()->input('search') . '%');
                });
            });
            return $this->success(PatientResource::collection($patient->take(10)->get()));
            // return $this->success(Cie10Resource::collection(Cie10::get()));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    public function paginate(Request $request)
    {
        try {
            $patient = Patient::with('eps', 'level', 'regimentype', 'typedocument', 'department', 'municipality')
                ->when($request->identifier, function ($query, $fill) {
                    $query->where('identifier', 'like', "%$fill%");
                })
                ->when($request->name, function ($query, $fill) {
                    $query->where(function ($query) use ($fill) {
                        $query->where(DB::raw('CONCAT_WS(" ", firstname, surname, secondsurname)'), "LIKE", "%" . $fill . "%")
                            ->orWhere(DB::raw('CONCAT_WS(" ", middlename, surname, secondsurname)'), "LIKE", "%" . $fill . "%")
                            ->orWhere(DB::raw('CONCAT_WS(" ", surname, secondsurname)'), "LIKE", "%" . $fill . "%")
                            ->orWhere(DB::raw('CONCAT_WS(" ", firstname, middlename, surname, secondsurname)'), "LIKE", "%" . $fill . "%");
                    });
                })
                ->when($request->eps, function ($query, $fill) {
                    $query->whereHas('eps', function ($query) use ($fill) {
                        $query->where('name', 'like', "%$fill%");
                    });
                })
                ->when($request->level_id, function ($query, $fill) {
                    $query->where('level_id', $fill);
                })
                ->when($request->regimentype_id, function ($query, $fill) {
                    $query->where('regimen_id', $fill);
                })
                ->when($request->state, function ($query, $fill) {
                    $query->where('state', $fill);
                })
                ->orderBy('firstname')
                ->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1));
            return $this->success($patient);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PatientSaveRequest $request)
    {

        try {
            $patient =   Patient::firstWhere('identifier', request('identifier'));
            request()->merge(['regional_id' => $this->appendRegional(request()->get('department_id'))]);

            if ($patient) {
                $patient->update($request->all());
            } else {
                $patient  = Patient::create(request()->all());
            }

            return $this->success(['message' => 'Actualizacion existosa', 'patient' => $patient]);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    public function show(Patient $patient)
    {
        $patientWithRelations = $patient->load('eps', 'regimentype', 'level');
        return $patientWithRelations;
    }

    /**
     * Update the specified resource in storage.
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Patient $patient)
    {
        try {
            $patient->update($request->all());
            return $this->success('Registro existoso');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }


    public function getPatientInCall()
    {
        try {
            $patient = null;
            $call = CallIn::Where('status', 'Pendiente')
                ->where('type', 'CallCenter')
                ->where('Identificacion_Agente', auth()->user()->usuario)
                ->first();
            if ($call) {
                $patient = Patient::with(
                    'eps',
                    'company',
                    'municipality',
                    'department',
                    'regional',
                    'level',
                    'regimentype',
                    'typedocument',
                    'contract',
                    'location'
                )->firstWhere('identifier', $call->Identificacion_Paciente);
            }
            return $this->success(['paciente' => $patient, 'llamada' => $call]);
        } catch (\Throwable $th) {
            return $this->success($th->getMessage(), 201);
        }
    }

    public function getPatientResend($id)
    {
        try {
            $patient = Patient::with(
                'eps',
                'company',
                'municipality',
                'department',
                'regional',
                'level',
                'regimentype',
                'typedocument',
                'contract',
                'location'
            )->firstWhere('identifier', $id);
            return $this->success($patient);
        } catch (\Throwable $th) {
            return $this->success($th->getMessage(), 201);
        }
    }

    public function charts()
    {
        $pacientes = Patient::join('regimen_types as r', 'r.id', '=', 'patients.regimen_id')
            ->select('r.name', DB::raw('count(*) as count'))
            ->groupBy('patients.regimen_id')
            ->get();
        $totalPacientes = Patient::count();
        foreach ($pacientes as $paciente) {
            $paciente->total = $totalPacientes;
            $paciente->percentage = round(($paciente->count / $totalPacientes) * 100, 2);
        }
        $conteoPorDepartamento = Patient::join('departments as d', 'd.id', '=', 'patients.department_id')
            ->select('d.name', DB::raw('count(patients.department_id) as count'))
            ->groupBy('patients.department_id')
            ->get();
        $eps = Patient::join('departments as d', 'd.id', '=', 'patients.department_id')
            ->join('eps as e', 'e.id', '=', 'patients.eps_id')
            ->select('e.name as EPS')
            ->groupBy('patients.eps_id')
            ->get();

        $statisticsByRegimen['eps'] = $eps;
        $statisticsByRegimen['data'] = $conteoPorDepartamento;

        $resultado['statistics'] = $pacientes;
        $resultado['statistics_by_regimen'] = $statisticsByRegimen;

        return $this->success($resultado);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
                'department_id' => 'required',
                'municipality_id' => 'required',
                'eps_id' => 'required',
                'regimen_id' => 'required',
                'state' => 'required',
            ]);
            $file = $request->file('file');
            $otherData = [
                'department_id' => $request->input('department_id'),
                'municipality_id' => $request->input('municipality_id'),
                'eps_id' => $request->input('eps_id'),
                'regimen_id' => $request->input('regimen_id'),
                'state' => $request->input('state'),
            ];
            $duplicateIdentifiers = [];
            Excel::import(new PatientsImport($otherData, $duplicateIdentifiers), $file);
            $response = new \stdClass();
            $response->duplicates = count($duplicateIdentifiers) > 0 ? true : false;
            $response->title = count($duplicateIdentifiers) > 0 ? 'Importación exitosa con excepciones' : 'Importación exitosa';
            $response->message = count($duplicateIdentifiers) > 0 ? 'No se han importado ' . count($duplicateIdentifiers) . ' registro(s).' : '';
            $response->duplicateIdentifiers = $duplicateIdentifiers;
            return $this->success($response);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Fila {$failure->row()}: {$failure->errors()[0]}";
            }
            return $this->error($errorMessages, 422);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function exportTemplate()
    {
        return Excel::download(new PatientsTemplateExport, 'pacientes_template.xlsx');
    }

    public function listaImpuestoMes()
{
    $impuestos = Impuesto::all();
    $meses = CompanyConfiguration::pluck('Expiration_Months');
    $bodegas = BodegaNuevo::select('Id_Bodega_Nuevo', 'Nombre')->get();

    $resultado = [
        'Impuesto' => $impuestos,
        'Meses' => $meses,
        'Bodega' => $bodegas,
    ];

    return response()->json($resultado);
}

    public function listaEps()
    {
        $query = 'SELECT *, id as value, name as text FROM eps WHERE nit IS NOT NULL
			ORDER BY name';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $eps = $oCon->getData();
        unset($oCon);

        return response()->json($eps);
    }

    public function listaMunicipios()
    {
        $id_dep = isset($_REQUEST['Dep']) ? $_REQUEST['Dep'] : false;

        $query = "SELECT id AS value, 
            name as text 
            FROM municipalities 
            WHERE department_id=$id_dep";

        $Con = new consulta();
        $Con->setQuery($query);
        $Con->setTipo('Multiple');
        $resultado = $Con->getData();
        unset($Con);

        return response()->json($resultado);
    }

    public function eliminarGenerico()
    {
        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');
        $estado = (isset($_REQUEST['estado']) ? $_REQUEST['estado'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        if ($mod == "Novedad") {
            $oItem = new complex($mod, "Id_" . $mod, $id);
            $oItem->delete();
            unset($oItem);
        } else {
            $oItem = new complex($mod, "Id_" . $mod, $id);
            $oItem->Estado = $estado;
            $oItem->save();
            unset($oItem);
            $mensaje['title'] = "Cambio de estado";
            $mensaje['message'] = "Se cambio el estado satisfactoriamente";
            $mensaje['type'] = "success";
        }

        return response()->json($mensaje);
    }
}
