<?php

namespace App\Http\Controllers;

use App\Models\AttentionCall;
use App\Models\CompanyConfiguration;
use App\Models\Person;
use App\Traits\ApiResponser;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttentionCallController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function callAlert($id)
    {
        try {
            $attentionExpiryDays = CompanyConfiguration::where('company_id', $this->getCompany())->first()->attention_expiry_days;
            $endDate = Carbon::now()->subDays($attentionExpiryDays)->format('Y-m-d');
            $attentionCalls = AttentionCall::where('person_id', $id)
                ->where('created_at', '>', $endDate)
                ->count();
            return $this->success($attentionCalls);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $attentionCall = AttentionCall::create([
                'reason' => $request->get('reason'),
                'number_call' => $request->get('number_call'),
                'person_id' => $request->get('person_id'),
                'user_id' => auth()->user()->id
            ]);
            $personName = $attentionCall->person->full_names;
            $data = (object) [
                'person_id' => $attentionCall->person_id,
                'title' => 'Nuevo llamado de atención',
                'description' => "Se creó un nuevo llamado de atención a {$personName}.",
                'icon' => 'fas fa-exclamation-circle',
                'type' => 'Llamado de atención',
                'historiable_type' => AttentionCall::class,
                'historiable_id' => $attentionCall->id,
            ];

            addHistory($data);
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function paginate(Request $request)
    {
        $data = $request->all();
        $page = key_exists('page', $data) ? $data['page'] : 1;
        $pageSize = key_exists('pageSize', $data) ? $data['pageSize'] : 5;
        return $this->success(AttentionCall::with('person')
            ->when($request->person_id, function ($query, $fill) {
                $query->whereHas('person', function ($query) use ($fill) {
                    $query->where('id', $fill);
                });
            })
            ->when($request->date, function ($query, $fill) {
                $query->where('created_at', 'like', "%$fill%");
            })
            ->whereHas('person.contractultimate', function ($query) {
                $query->where('company_id', $this->getCompany());
            })
            ->orderByDesc('created_at')
            ->paginate($pageSize, ['*'], 'page', $page));
    }

    public function download($id, $memorandum = false)
    {
        $attentionCall = AttentionCall::with('person', 'user')->find($id);
        $datosCabecera = (object) array(
            'Titulo' => 'Llamado de atención',
            'Codigo' => $attentionCall->code ?? '',
            'Fecha' => $attentionCall->created_at,
            'CodigoFormato' => $attentionCall->format_code ?? ''
        );
        $pdf = PDF::loadView('pdf.attention_call', [
            'data' => $attentionCall,
            'datosCabecera' => $datosCabecera,
        ]);
        if ($memorandum) {
            return $pdf->download('llamado-de-atencion.pdf');
        } else {
            return $pdf->stream('llamado-de-atencion.pdf');
        }
    }
}
