<?php

namespace App\Http\Controllers;

// use App\CallIn;

use App\Models\CallIn;
use App\Models\Patient;
use App\Models\WaitingList;
use App\Traits\ApiResponser;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

class CallInController extends Controller
{
    use ApiResponser;

    public function presentialCall(Request $request)
    {
        try {
            $data = $request->all();
            $call = CallIn::create($data);
            $patient = Patient::with('eps', 'company', 'municipality', 'department', 'regional', 'level', 'regimentype', 'typedocument', 'contract')->firstWhere('identifier', $call->Identificacion_Paciente);
            $isNew = false;
            if (!$patient) {
                $isNew = true;
            }
            return $this->success(['paciente' => $patient, 'llamada' => $call, 'isNew' => $isNew]);
        } catch (\Throwable $th) {
            return $this->success($th->getMessage(), 201);
        }
    }

    public function patientforwaitinglist(Request $request)
    {
        try {
            $data = WaitingList::with('appointment', 'appointment.callin', 'appointment.cup:description As text,id,id As value', 'appointment.cie10:description As text,id,id As value')->find(request()->get('0'));
            $patient = Patient::with('eps', 'company', 'municipality', 'department', 'regional', 'level', 'regimentype', 'typedocument', 'contract')->firstWhere('identifier', $data->appointment->callin->Identificacion_Paciente);
            $isNew = false;
            return $this->success(['paciente' => $patient, 'llamada' => $data->appointment->callin, 'isNew' => $isNew, 'anotherData' => $data]);
        } catch (\Throwable $th) {
            return $this->success($th->getMessage(), 400);
        }
    }


    public function getCallByIdentifier(Request $request)
    {
        try {
            $calls = CallIn::with('usuario.person')
                ->when($request->identifier, function ($query, $fill) {
                    $query->where('Identificacion_Paciente', $fill);
                })
                ->paginate(50);
            return response()->success($calls);
        } catch (\Throwable $th) {
            return $this->success($th->getMessage(), 400);
        }
    }
}
