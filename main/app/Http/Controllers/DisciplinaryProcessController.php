<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryClosure;
use App\Models\DisciplinaryProcess;
use App\Models\DisciplinaryProcessAction;
use App\Models\LegalDocument;
use App\Models\MemorandumInvolved;
use App\Models\Person;
use App\Models\PersonInvolved;
use App\Traits\ApiResponser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ZipArchive;

use Illuminate\Support\Str;

class DisciplinaryProcessController extends Controller
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
    public function index()
    {
        $involved = request()->get('involved');
        $query = DisciplinaryProcess::with([
            'person' => function ($q) {
                $q->select('id', 'first_name', 'second_name', 'first_surname', 'second_surname');
            },
            'personInvolved',
            'personInvolved.person'
        ])
            ->whereHas('person', function ($q) {
                $q->when(request()->get('person'), function ($q, $fill) {
                    $q->where(DB::raw('concat(first_name, " ",first_surname)'), 'like', '%' . $fill . '%');
                });
            })
            ->whereHas('person.contractultimate', function ($query) {
                $query->where('company_id', $this->getCompany());
            })
            ->when(request()->get('status'), function ($q, $fill) {
                $q->where('status', 'like', '%' . $fill . '%');
            })
            ->when(request()->get('code'), function ($q, $fill) {
                $q->where('code', 'like', '%' . $fill . '%');
            })
            ->when($involved, function ($q) {
                $q->whereHas('personInvolved.person', function ($q) {
                    $q->where(DB::raw('concat(first_name, " ",first_surname)'), 'like', '%' . request()->get('involved') . '%');
                });
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));
        return $this->success($query);
    }

    public function getMemorandumsForPeople($id)
    {
        return $this->success(
            DB::table('memorandums as m')
                ->select(
                    'm.id',
                    'm.created_at as created_at_memorandum',
                    't.name as memorandumType',
                    'p.first_name',
                    'm.details'
                )
                ->join('people as p', function ($join) {
                    $join->on('p.id', '=', 'm.person_id');
                })
                ->join('memorandum_types as t', function ($join) {
                    $join->on('t.id', '=', 'm.memorandum_type_id');
                })
                ->where('p.id', '=', $id)
                ->get()
        );
    }

    public function show($id)
    {
        $data = DisciplinaryProcess::with([
            'person',
            'personInvolved' => function ($query) {
                $query->where('state', 'Activo');
            },
            'personInvolved.person',
            'personInvolved.user',
            'personInvolved.memorandumInvolved',
            'legalDocuments' => function ($query) {
                $query->where('state', 'Activo');
            },
            'closure.user',
            'closure.closureReason',
            'actions.user'
        ])->find($id);
        $data->history = $this->history($id);
        return $this->success($data);
    }

    public function saveActions(Request $request)
    {

        $action = new DisciplinaryProcessAction();
        $action->user_id = auth()->user()->id;
        $action->disciplinary_process_id = $request->disciplinary_process_id;
        $action->description = $request->description;
        if ($request->file) {
            $base64 = saveBase64File($request->file, 'actuaciones/', false, '.pdf');
            $url = URL::to('/') . '/api/image?path=' . $base64;
            $action->file = $url;
        }
        $action->action_type_id = $request->action_type_id;
        $action->date = $request->date;
        $action->save();
        $data = (object) [
            'person_id' => 1,
            'title' => 'Nueva actuación',
            'description' => "Se creó una nueva actuación.",
            'icon' => 'fas fa-exclamation-circle',
            'type' => 'Actuación',
            'historiable_type' => DisciplinaryProcess::class,
            'historiable_id' => $request->disciplinary_process_id,
        ];
        addHistory($data);
        return $this->success('Creado exitosamente');
    }

    private function history($id)
    {
        $disciplinaryProcess = DisciplinaryProcess::with([
            'histories',
            'memorandum' => function ($query) {
                $query->with([
                    'histories',
                    'attentionCalls' => function ($query) {
                        $query->with(['histories']);
                    }
                ]);
            },
        ])->find($id);
        $disciplinaryProcessHistories = $disciplinaryProcess->histories;
        if ($disciplinaryProcess->memorandum) {
            $memorandumHistories = $disciplinaryProcess->memorandum->histories;
            $disciplinaryProcessHistories = $disciplinaryProcessHistories->merge($memorandumHistories);
            foreach ($disciplinaryProcess->memorandum->attentionCalls as $attentionCall) {
                $attentionCallHistories = $attentionCall->histories;
                $disciplinaryProcessHistories = $disciplinaryProcessHistories->merge($attentionCallHistories);
            }
        }
        $disciplinaryProcessHistories = $disciplinaryProcessHistories->sortByDesc('created_at', SORT_REGULAR)->values();
        return $disciplinaryProcessHistories;
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $data = $request->except(['involved']);
            $involves = request()->get('involved');
            $code = generateConsecutive('disciplinary_processes');
            $files = $request->file;
            $dp = DisciplinaryProcess::create([
                'person_id' => $request->person_id,
                'process_description' => $request->process_description,
                'date_of_admission' => $request->date_of_admission,
                'date_end' => $request->date_end,
                'code' => $code,
                'title' => $request->title,
            ]);
            if ($request->filled('file')) {
                foreach ($files as $file) {
                    $base64 = $this->saveFiles($file, 'legal_documents/');
                    $url = URL::to('/') . '/api/file?path=' . $base64;

                    LegalDocument::create([
                        'disciplinary_process_id' => $dp->id,
                        'file' => $url,
                        'name' => $file['name'],
                        'type' => $file['type'],
                    ]);
                }
            }
            $personNameDP = $dp->person->full_names;
            $data = (object) [
                'person_id' => $dp->person_id,
                'title' => 'Nuevo proceso disciplinario',
                'description' => "Se creó un nuevo proceso disciplinario a {$personNameDP}.",
                'icon' => 'fas fa-exclamation-circle',
                'type' => 'Proceso disciplinario',
                'historiable_type' => DisciplinaryProcess::class,
                'historiable_id' => $dp->id,
            ];
            addHistory($data);
            foreach ($involves as $involved) {
                $type = '.' . $involved['type'];
                if ($involved['type'] == 'jpeg' || $involved['type'] == 'jpg' || $involved['type'] == 'png') {
                    $base64 = saveBase64($involved["file"], 'evidencia/', true);
                    $urlInvolved = URL::to('/') . '/api/image?path=' . $base64;
                } else {
                    $base64 = saveBase64File($involved["file"], 'evidencia/', false, $type);
                    $urlInvolved = URL::to('/') . '/api/file?path=' . $base64;
                }
                $annotation = PersonInvolved::create([
                    'user_id' => auth()->user()->id,
                    'observation' => $involved['observation'],
                    'file' => $urlInvolved,
                    'disciplinary_process_id' => $dp->id,
                    'person_id' => $involved['person_id'],
                    'file_type' => $involved["type"]
                ]);
                foreach ($involved['memorandums'] as $memorandum) {
                    MemorandumInvolved::create([
                        'person_involved_id' => $annotation['id'],
                        'memorandum_id' => $memorandum['id']
                    ]);
                }
            }
            sumConsecutive('disciplinary_processes');
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error([$th->getMessage(), $th->getLine(), $th->getFile()], 500);
        }
    }

    public function descargoPdf($id)
    {
        $descargo = DB::table('disciplinary_processes as dp')
            ->select(
                'dp.person_id',
                'dp.id as descargo_id',
                'p.first_name',
                'p.second_name',
                'p.first_surname',
                'p.second_surname',
                'p.identifier',
                'dp.date_of_admission',
                'dp.created_at'
            )
            ->join('people as p', 'p.id', '=', 'dp.person_id')
            ->where('dp.id', $id)
            ->first();
        $company = DB::table('companies as c')
            ->select(
                'c.name as company_name',
                'c.document_number as nit',
                'c.phone',
                'c.email_contact',
                DB::raw('CURRENT_DATE() as fecha')
            )
            ->first();
        $pdf = PDF::loadView('pdf.descargopdf', [
            'descargo' => $descargo,
            'company' => $company
        ]);
        return $pdf->download('descargopdf.pdf');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function process($id)
    {
        return $this->success(
            DB::table('disciplinary_processes as d')
                ->select(
                    'd.id',
                    'd.process_description',
                    'd.created_at as created_at_process'
                )
                ->join('people as p', function ($join) {
                    $join->on('p.id', '=', 'd.person_id');
                })
                ->where('p.id', '=', $id)
                ->get()
        );
    }

    public function saveLegalDocument(Request $request)
    {
        try {
            $data = $request->all();
            $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'mp3', 'wav', 'mp4'];
            foreach ($data as $item) {
                $type = '.' . $item['type'];
                $extension = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    throw new \Exception('Tipo de archivo no permitido: ' . $extension);
                }
                if ($item['type'] == 'jpeg' || $item['type'] == 'jpg' || $item['type'] == 'png') {
                    $base64 = saveBase64($item['file'], 'legal_documents/', true);
                    $url = URL::to('/') . '/api/image?path=' . $base64;
                } else {
                    $base64 = saveBase64File($item['file'], 'legal_documents/', false, $type);
                    $url = URL::to('/') . '/api/file?path=' . $base64;
                }
                LegalDocument::create([
                    'file' => $url,
                    'disciplinary_process_id' => $item['disciplinary_process_id'],
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'motivo' => $item['motivo']
                ]);
                $data = (object) [
                    'title' => 'Nuevo documento',
                    'description' => "Se ha agregado un nuevo documento al proceso.",
                    'icon' => 'fas fa-exclamation-circle',
                    'type' => 'Documento',
                    'person_id' => 1,
                    'historiable_type' => DisciplinaryProcess::class,
                    'historiable_id' => $item['disciplinary_process_id'],
                ];
                addHistory($data);
            }
            return $this->success("Guardado con éxito");
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
        }
    }

    public function InactiveDOcument(Request $request, $id)
    {
        try {
            $doc = LegalDocument::where('id', '=', $id)->first();
            $doc->update($request->all());
            return $this->success('Estado cambiado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }

    public function legalDocument($id)
    {
        return $this->success(
            LegalDocument::where('disciplinary_process_id', $id)->where('state', 'Activo')->get()
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Conseguimos el objeto
        $proceso = DisciplinaryProcess::where('id', '=', $id)->first();
        try {
            // Si existe
            $proceso->fill($request->all());
            $proceso->save();
            // $base64 = saveBase64File($request->file, 'legal_documents/', false, '.pdf');
            // URL::to('/') . '/api/file?path=' . $base64;
            // LegalDocument::create([
            //     'file' => $base64,
            //     'disciplinary_process_id' => $id
            // ]);
            return $this->success($proceso);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error($proceso, 500);
        }
    }

    public function close(Request $request)
    {
        $request->validate([
            'disciplinary_closure_reasons_id' => 'required',
            'disciplinary_process_id' => 'required',
            'description' => 'required',
            'file' => 'required'
        ]);
        try {
            $personName = Person::imageName()->find(Auth()->user()->person_id)->full_names;
            $data = $request->all();
            $base64 = saveBase64File($request->file, 'disciplinary_closures/', false, '.pdf');
            $url = URL::to('/') . '/api/file?path=' . $base64;
            $data['file'] = $url;
            $data['user_id'] = auth()->user()->id;
            DisciplinaryClosure::create($data);
            DisciplinaryProcess::find($request->disciplinary_process_id)->update(['status' => 'Cerrado']);
            $data = (object) [
                'person_id' => 1,
                'title' => 'Cierre de proceso disciplinario',
                'description' => "{$personName} ha cerrado el proceso disciplinario.",
                'icon' => 'fas fa-exclamation-circle',
                'type' => 'Cierre',
                'historiable_type' => DisciplinaryProcess::class,
                'historiable_id' => $request->disciplinary_process_id,
            ];
            addHistory($data);
            return $this->success("Cerrado con exito");
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }


    public function approve(Request $request, $id)
    {
        try {
            $approve = DisciplinaryProcess::find($id);
            $approve->update([
                'status' => $request->status,
                'approve_user_id' => auth()->user()->id
            ]);
            return $this->success("Aprobado con éxito");
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
        }
    }

    public function download($id, Request $request)
    {
        $disciplinaryProcess = DisciplinaryProcess::with('person')->find($id);
        $datosCabecera = (object) [
            'Titulo' => 'Llamado a descargos',
            'Codigo' => $disciplinaryProcess->code ?? '',
            'Fecha' => $disciplinaryProcess->created_at,
            'CodigoFormato' => $disciplinaryProcess->format_code ?? ''
        ];
        if ($request->type === 'personal') {
            $pdf = PDF::loadView('pdf.disciplinary_notice', [
                'data' => $disciplinaryProcess,
                'datosCabecera' => $datosCabecera,
                'content' => $request->content,
                'person' => $disciplinaryProcess->person->full_names
            ]);
            return $pdf->download('llamado-a-descargos.pdf');
        } elseif ($request->type === 'tercero') {
            $involved = $disciplinaryProcess->personInvolved()->find($request->involved_id);
            $pdf = PDF::loadView('pdf.disciplinary_notice', [
                'data' => $disciplinaryProcess,
                'datosCabecera' => $datosCabecera,
                'content' => $request->content,
                'person' => $involved->person->full_names
            ]);
            return $pdf->download('llamado-a-descargos.pdf');
        } else {
            $involveds = $disciplinaryProcess->personInvolved()->get();
            $zipFileName = 'involucrados.zip';
            $zip = new ZipArchive;
            $filesToZip = [];

            if ($zip->open(public_path($zipFileName), ZipArchive::CREATE) === TRUE) {
                foreach ($involveds as $key => $involved) {
                    $pdf = PDF::loadView('pdf.disciplinary_notice', [
                        'data' => $involved,
                        'datosCabecera' => $datosCabecera,
                        'content' => $request->content,
                        'person' => $involved->person->full_names
                    ]);
                    $name = 'descargos-' . str_replace(' ', '-', strtolower($involved->person->full_names));
                    Storage::put('pdf/' . $name . '.pdf', $pdf->output());
                    $filesToZip[] = public_path('app/pdf/' . $name . '.pdf');
                }

                foreach ($filesToZip as $file) {
                    $zip->addFile($file, basename($file));
                }

                $zip->close();

                foreach ($filesToZip as $file) {
                    unlink($file);
                }

                if (count($involveds) > 0) {
                    return response()->download(public_path($zipFileName))->deleteFileAfterSend(true);
                } else {
                    return $this->error('Este proceso disciplinario no tiene terceros involucrados', 404);
                }
            } else {
                return "Failed to create the zip file.";
            }
        }
    }
}
