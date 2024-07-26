<?php

namespace App\Http\Controllers;

use App\Models\AttentionCall;
use App\Models\CompanyConfiguration;
use App\Models\DisciplinaryProcess;
use App\Models\Memorandum;
use App\Models\MemorandumFile;
use App\Models\Person;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MemorandumController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }


    public function index()
    {
        return $this->success(
            Memorandum::all()
        );
    }

    public function getMemorandum(Request $request)
    {
        $data = $request->all();
        $page = key_exists('page', $data) ? $data['page'] : 1;
        $pageSize = key_exists('pageSize', $data) ? $data['pageSize'] : 100;
        return $this->success(Memorandum::with('person', 'memorandumtype', 'files', 'disciplinaryProcess')
            ->when($request->person_id, function ($query, $fill) {
                $query->whereHas('person', function ($query) use ($fill) {
                    $query->where('id', $fill);
                });
            })
            ->when($request->date, function ($query, $fill) {
                $query->where('created_at', 'like', "%$fill%");
            })
            ->when($request->state, function ($query, $fill) {
                $query->where('state', $fill);
            })
            ->whereHas('person.contractultimate', function ($query) {
                $query->where('company_id', $this->getCompany());
            })
            ->orderByDesc('created_at')
            ->paginate($pageSize, ['*'], 'page', $page));
    }

    function saveFiles($file, $path)
    {
        $file_info = $file;
        $extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ["pdf", "png", "jpeg", "jpg", "doc", "docx", "xlsx", "mp3", "mp4", "wav", "mkv"];
        if (!in_array(strtolower($extension), $allowed_extensions)) {
            return response()->json(['error' => 'Tipo de archivo no permitido'], 422);
        }
        $file_content = base64_decode(
            preg_replace(
                "#^data:[a-z]+/[\w\+]+;base64,#i",
                "",
                $file['file']
            )
        );
        $file_path = $path . Str::random(30) . time() . '.' . $extension;
        Storage::disk()->put($file_path, $file_content, "public");
        return $file_path;
    }


    public function store(Request $request)
    {
        $files = $request->file;
        $companyConfiguration = CompanyConfiguration::where('company_id', $this->getCompany())->first();
        $attentionExpiryDays = $companyConfiguration->attention_expiry_days;
        $maxMemosPerEmployee = $companyConfiguration->max_memos_per_employee;
        $memorandums = Memorandum::where('person_id', $request->person_id)->count();
        if ($memorandums >= $maxMemosPerEmployee) {
            return $this->error('El empleado ya tiene el maximo de memorandos permitidos', 500);
        }
        try {
            $endDate = Carbon::now()->subDays($attentionExpiryDays)->format('Y-m-d');

            $attentionCalls = AttentionCall::where('person_id', $request->person_id)
                ->where('created_at', '>', $endDate)
                ->pluck('id');

            $memorandum = Memorandum::updateOrCreate(
                [
                    'id' => $request->get('id'),
                    'approve_user_id' => auth()->user()->id,
                ],
                [
                    'level' => $request->level,
                    'details' => $request->details,
                    'memorandum_type_id' => $request->memorandum_type_id,
                    'person_id' => $request->person_id
                ]
            );
            if ($request->filled('file')) {
                foreach ($files as $file) {
                    $base64 = $this->saveFiles($file, 'legal_documents/');
                    $url = URL::to('/') . '/api/file?path=' . $base64;

                    MemorandumFile::create([
                        'memorandum_id' => $memorandum->id,
                        'file' => $url,
                        'name' => $file['name'],
                        'type' => $file['type'],
                    ]);
                }
            }

            $personName = $memorandum->person->full_names;
            $data = (object) [
                'person_id' => $memorandum->person_id,
                'title' => 'Nuevo memorando',
                'description' => "Se creó un nuevo memorando a {$personName}.",
                'icon' => 'fas fa-exclamation-circle',
                'type' => 'Memorando',
                'historiable_type' => Memorandum::class,
                'historiable_id' => $memorandum->id,
            ];

            addHistory($data);

            if (count($attentionCalls) == 3) {
                $memorandum->attentionCalls()->sync($attentionCalls);
            }

            $code = generateConsecutive('disciplinary_processes');

            $disciplinaryProcess = DisciplinaryProcess::create([
                'code' => $code,
                'person_id' => $request->person_id,
                'process_description' => $request->details,
                'date_of_admission' => $memorandum->created_at,
                'memorandum_id' => $memorandum->id,
                'title' => 'Proceso creado automáticamente ligado a un memorando'
            ]);

            $personNameDP = $disciplinaryProcess->person->full_names;
            $dataPD = (object) [
                'person_id' => $disciplinaryProcess->person_id,
                'title' => 'Nuevo proceso disciplinario',
                'description' => "Se creó un nuevo proceso disciplinario a {$personNameDP}.",
                'icon' => 'fas fa-exclamation-circle',
                'type' => 'Proceso disciplinario',
                'historiable_type' => DisciplinaryProcess::class,
                'historiable_id' => $disciplinaryProcess->id,
            ];
            addHistory($dataPD);
            sumConsecutive('disciplinary_processes');
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function download($id)
    {
        $memorandum = Memorandum::with('person', 'memorandumtype', 'attentionCalls', 'approveUser')->find($id);
        $datosCabecera = (object) array(
            'Titulo' => 'Memorando',
            'Codigo' => $memorandum->code || '',
            'Fecha' => $memorandum->created_at,
            'CodigoFormato' => $memorandum->format_code || ''
        );
        foreach ($memorandum->attentionCalls as $attentionCall) {
            $attentionCall->datosCabecera = (object) array(
                'Titulo' => 'Llamado de atención',
                'Codigo' => $attentionCall->code ?? '',
                'Fecha' => $attentionCall->created_at,
                'CodigoFormato' => $attentionCall->format_code ?? ''
            );
        }
        //return $memorandum;
        $pdf = PDF::loadView('pdf.memorandum', [
            'dataMemo' => $memorandum,
            'datosCabecera' => $datosCabecera,
        ]);
        return $pdf->download('memorando.pdf');
    }
}
