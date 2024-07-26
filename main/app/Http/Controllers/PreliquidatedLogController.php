<?php

namespace App\Http\Controllers;

use App\Models\PreliquidatedLog;
use App\Models\Person;
use App\Models\WorkContract;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PreliquidatedLogController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        try {
            $people_liq = Person::with('onePreliquidatedLog.workContractBT', 'contractultimate')
                ->when($request->person_id, function ($q, $fill) {
                    $q->where('id', $fill);
                })
                ->whereHas('onePreliquidatedLog.workContractBT', function ($query) {
                    $query->where('company_id', $this->getCompany());
                })
                ->fullName()
                ->where('status', 'PreLiquidado')
                ->paginate($request->get('pageSize', 10), ['*'], 'page', $request->get('page', 1));

            return $this->success($people_liq);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 204);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $message = '';
        try {
            if ($request->status == 'PreLiquidado') {
                $work_contract = WorkContract::find($request->contract_work);
                $work_contract->update([
                    'liquidated' => 1,
                    'date_end' => $request->liquidated_at,
                    'old_date_end' => $work_contract->date_end
                ]);
                $responsable = $request->reponsible;
                $nuevo = PreliquidatedLog::create([
                    "person_id" => $request->id,
                    "person_identifier" => $request->identifier,
                    "full_name" => $request->full_name,
                    "liquidated_at" => $request->liquidated_at,
                    "person_work_contract_id" => $request->contract_work,
                    "reponsible_id" => $responsable['person_id'],
                    "responsible_identifier" => $responsable['usuario'],
                    "status" => $request->status,
                ]);
                $message = 'Funcionario preliquidado exitosamente';
            } else if ($request->status == 'Reincorporado') {
                $log = PreliquidatedLog::where('person_id', $request->id)->latest()->first();
                $work_contract = WorkContract::find($log->person_work_contract_id);
                $work_contract->update([
                    'liquidated' => 0,
                    'date_end' => $work_contract->old_date_end,
                    'old_date_end' => null
                ]);
                $responsable = $request->reponsible;
                $nuevo = PreliquidatedLog::create([
                    "person_id" => $request->id,
                    "person_identifier" => $request->identifier,
                    "full_name" => $request->full_name,
                    "liquidated_at" => $request->liquidated_at,
                    "person_work_contract_id" => $log->person_work_contract_id,
                    "reponsible_id" => $responsable['person_id'],
                    "responsible_identifier" => $responsable['usuario'],
                    "status" => $request->status,
                ]);
                $message = 'Funcionario reincorporado exitosamente';
            }
            return $this->success([
                'status' => 'success',
                'message' => $message,
                'response' => $nuevo
            ]);
        } catch (\Throwable $th) {
            return $this->error([
                'status' => 'error',
                'message' => "Ha ocurrido un error inesperado",
                'data' => $th->getMessage() . ' line: ' . $th->getLine() . ' ' . $th->getFile()
            ], 204);
        }
    }
}
